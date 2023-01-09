<?php

namespace App\Controller;

use App\Entity\Client;
use App\Services\FormatageObjet;
use App\Entity\Expediteur;
use App\Form\ExpediteurType;
use App\Repository\ClientRepository;
use App\Repository\ExpediteurRepository;
use App\Services\DataFinder;
use App\Services\FormattingService;
use App\Services\MessageService;
use App\Services\MailService;
use App\Services\RequestManager;
use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Firebase\JWT\JWT;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/*
Cette classe donne la possibilité de créer, modifier, activer et supprimer un expéditeur.
*/

#[Route('/', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class ExpediteurController extends AbstractController
{
    private $requestManager, $dataFinder, $formattingService, $messageService, $mailService;

    public function __construct(
        RequestManager $requestManager,
        DataFinder $dataFinder,
        FormattingService $formattingService,
        MessageService $messageService,
        MailService $mailService
    ) {
        $this->requestManager = $requestManager;
        $this->dataFinder = $dataFinder;
        $this->formattingService = $formattingService;
        $this->messageService = $messageService;
        $this->mailService = $mailService;
    }

    /*
        Retourne un template twig avec la liste de tous les expéditeurs
    */
    #[Route('/expediteurs', name: 'expediteur')]
    public function index(
        Request $request
    ): Response {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $data = $this->dataFinder->GetExpediteurs($request);

        $dataPagination = $this->dataFinder->Paginate($data, $request);

        return $this->render('expediteur/index.html.twig', $this->requestManager->GenerateRenderRequest("expediteur", $request, $dataPagination, $data));
    }

    /* 
        La méthode ajouter permet de créer un expéditeur inactif et de lui envoyer un lien de confirmation par mail fin de configurer son mot de passe.
    */
    #[Route('/ajouter', name: 'addExpediteur')]
    public function new(Request $request, MailerInterface $mailer, ExpediteurRepository $expediteurRepo): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $serializer = new Serializer([new ObjectNormalizer()]);

        $form = $this->createForm(ExpediteurType::class, null, ['type' => 'create']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // vérification du code postal et numéro téléphone
            try {
                if (strlen(intval($form->get('codePostal')->getData())) != 5) {
                    throw new Exception("Le code postal est incorrect");
                }
                if (strlen(intval($form->get('telephone')->getData())) < 9) {
                    throw new Exception("Le numéro de téléphone est incorrect");
                }
            } catch (Exception $e) {
                return $this->redirectToRoute('app_addExpediteur', [
                    'errorMessage' => $e->getMessage(),
                    'isError' => true
                ]);
            }

            $timezone = new DateTimeZone('UTC');

            $expediteur = $form->getData();
            $expediteur->setClient(null);
            $expediteurArray = $this->formattingService
                ->stringToLowerObject(
                    $expediteur,
                    Expediteur::class,
                    array('client', 'createdAt', 'updatedAt'),
                    true
                );
            $nbHeureExp = 24;
            $token = (new JWT())->encode(
                $expediteurArray,
                $_ENV['PASS_PHRASE'], // pass phrase
                'HS256', // protocole d'encodage
                head: ['exp' => time() + (3600 * $nbHeureExp)]
            );
            $expInHtml = $nbHeureExp == 1 ? " heure </p>" : " heures </p>";
            $body = "
            <p> Bonjour" . ($form->get('prenom')->getData() != null ? " " . $form->get('prenom')->getData() . ",</p>" : ",</p>") . "<p>veuillez confirmer la création de votre compte client associé à l'email " . $form->get('email')->getData() . " avec le bouton se trouvant ci-dessous. </p>
            <p><a href='https://step-post.fr/profil/validation-nouveau-compte?token=" . $token . "'> Confirmer la création de mon compte client </a></p>
            <p> La confirmation va expirer dans " . $nbHeureExp . $expInHtml . $this->mailService->getSignature();


            try {
                $expediteur = $serializer->denormalize($expediteurArray, Expediteur::class);
                $expediteur->setCreatedAt(new DateTime('now', $timezone))->setUpdatedAt(new DateTime('now', $timezone))->setRoles(['ROLE_INACTIF'])->setPassword(' ');
                $expediteurRepo->add($expediteur->setClient($form->get("addClient")->getData()), true);
            } catch (UniqueConstraintViolationException) {
                return $this->redirectToRoute('app_addExpediteur', $this->messageService->GetErrorMessage("Expéditeur", 1));
            }

            try {
                $mail = (new Email())
                    ->from($this->formattingService->formatMailFromEnv($_ENV["MAILER_DSN"])) // adresse de l'expéditeur de l'email ayant son email de configuré dans le .env
                    ->to($form->get('email')->getData())
                    ->subject('Création de votre compte client')
                    ->html($body);
                $mailer->send($mail);
                return $this->redirectToRoute('app_expediteur', $this->messageService->GetSuccessMessage("Expéditeur", 1));
            } catch (TransportExceptionInterface $e) {
                // supprimer le compte expéditeur créé si envoi raté de l'email
                return $this->redirectToRoute('app_addExpediteur', $this->messageService->GetErrorMessage("Expéditeur", 2));
            }
        }

        return $this->renderForm('expediteur/new.html.twig', [
            'form' => $form,
            'expediteursInactifs' => $expediteurRepo->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }


    /* 
        La méthode edit permet de modifier les informations d'un expéditeur
    */
    #[Route('/edit/{id}', name: 'editExpediteur')]
    public function edit(Request $request, Expediteur $ancienExpediteur, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ExpediteurType::class, $ancienExpediteur, [
            "type" => "edit",
            "clientTemp" => $ancienExpediteur->getClient() != null ? $ancienExpediteur->getClient()->getRaisonSociale() : 'Aucune raison sociale définie par le client'
        ]);
        $form->handleRequest($request);

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Expéditeur"]["Modification"];
        $messageErreur = $messages["Messages Erreurs"]["Expéditeur"]["Modification"];

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (strlen(intval($form->get('codePostal')->getData())) != 5) {
                    throw new Exception("Le code postal est incorrect");
                }
                if (strlen(intval(str_replace(" ", "", $form->get('telephone')->getData()))) < 9) {
                    throw new Exception("Le numéro de téléphone est incorrect");
                }
            } catch (Exception $e) {
                return $this->redirectToRoute('app_editExpediteur', [
                    'errorMessage' => $e->getMessage(),
                    'isError' => true,
                    'id' => $ancienExpediteur->getId()
                ]);
            }
            $ancienExpediteur->setClient(null);
            $expediteur = $this->formattingService->stringToLowerObject(
                $ancienExpediteur,
                Expediteur::class,
                array('client')
            );
            try {
                /* $expediteur = $this->formattingService->stringToLowerObject(
                    $ancienExpediteur,
                    Expediteur::class,
                    array('client')
                ); */
                $em->persist($expediteur->setClient($form->get('addClient')->getData()));
                $em->flush();
                return $this->redirectToRoute('app_expediteur', ['errorMessage' => str_replace('[nom]', $expediteur->getNom(), $message)], Response::HTTP_SEE_OTHER);
            } catch (Exception $e) {
                return $this->redirectToRoute('app_editExpediteur', ['errorMessage' => str_replace('[nom]', $ancienExpediteur->getNom(), $messageErreur), 'isError' => true, 'id' => $ancienExpediteur->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('expediteur/edit.html.twig', [
            'expediteur' => $ancienExpediteur,
            'form' => $form,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    /* 
        La méthode Delete permet de supprimer un Expéditeur
    */
    #[Route('/delete/{id}', name: 'deleteExpediteur')]
    public function Delete(Expediteur $expediteur, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Expéditeur"]["Suppression"];
        $messageErreur = $messages["Messages Erreurs"]["Expéditeur"]["Suppression"];

        try {
            $expediteur->setRoles(["ROLE_DELETED"]);
            $em->persist($expediteur);
            $em->flush();
            return $this->redirectToRoute('app_expediteur', ['errorMessage' => str_replace('[nom]', $expediteur->getNom(), $message), Response::HTTP_SEE_OTHER]);
        } catch (Exception) {
            return $this->redirectToRoute('app_expediteur', ['errorMessage' => str_replace('[nom]', $expediteur->getNom(), $messageErreur), 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }

    /* 
        La méthode MultipleDelete permet de supprimer un Expéditeur
        ...Mis en suspens...
    */
    /* #[Route('/delete', name: 'deleteMultipleExpediteur')]
    public function MultipleDelete(ExpediteurRepository $expediteurRepository, EntityManagerInterface $manager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Expéditeur"]["Suppression"];
        $messageErreur = $messages["Messages Erreurs"]["Expéditeur"]["Suppression"];

        $expediteursToKeep = $expediteurRepository->findExpediteurToKeep(new DateTime('now'));
        $i = 0;
        foreach ($expediteurRepository->findAll() as $expediteur) {
            in_array($expediteur->getId(), $expediteursToKeep[$i] ?? [null]) ? NULL : $expediteurRepository->remove($expediteur, false);
            $i++;
        }

        try {
            $manager->flush();
            return $this->redirectToRoute('app_expediteur', ['errorMessage' => str_replace('[nom]', $expediteur->getNom(), $message), Response::HTTP_SEE_OTHER]);
        } catch (Exception) {
            return $this->redirectToRoute('app_expediteur', ['errorMessage' => str_replace('[nom]', $expediteur->getNom(), $messageErreur), 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    } */


    /* 
        La méthode Details récupère et affiche tous les détails d'un expéditeur
    */
    #[Route('/detailsExpediteur', name: 'detailsExpediteur')]
    public function Details(Request $request, ExpediteurRepository $expediteurRepository): Response
    {


        $expediteurId = $request->get('expediteurId');
        $expediteur = $expediteurRepository->find($expediteurId);
        return $this->render('expediteur/details.html.twig', [
            'expediteur' => $expediteur,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false,
            'recherche' => $request->get('recherche'),
            'dateMin' => $request->get('dateMin'),
            'dateMax' => $request->get('dateMax')
        ]);
    }

    /* 
        Cette méthode change le rôle d'un expéditeur à ROLE_CLIENT
    */
    #[Route('/activer', name: 'activateExpediteur')]
    public function Activate(Request $request, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em, MailerInterface $mailer, ClientRepository $clientRepository): RedirectResponse
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Expéditeur"]["Activation"];
        $messageErreur = $messages["Messages Erreurs"]["Expéditeur"]["Activation"];

        $expediteurId = $request->get('expediteurId');
        $expediteur = $expediteurRepository->find($expediteurId);
        $client = $expediteur->getClient();
        $client->setRaisonSociale(str_replace("tmp_", "", $client->getRaisonSociale()));
        $em->persist($client);
        $email = (new Email())
            ->from('step.automaticmailservice@gmail.com')
            ->subject('Activation de votre compte Step Post')
            ->to($expediteur->getEmail())
            ->html("<p>Votre compte associé à l'adresse mail " . $expediteur->getEmail() . " a été activé. Vous pouvez donc vous connecter à adresse : </p><a href='https://step-post.fr'>https://step-post.fr</a>");

        try {
            $em->persist($expediteur->setRoles(['ROLE_CLIENT']));
            $em->flush();
            $mailer->send($email);
            return $this->redirectToRoute('app_expediteur', ['errorMessage' => str_replace('[nom]', $expediteur->getNom(), $message)]);
        } catch (Exception $e) {
            return $this->redirectToRoute('app_expediteur', ['errorMessage' => str_replace('[nom]', $expediteur->getNom(), $messageErreur), 'isError' => true]);
        }
    }
}
