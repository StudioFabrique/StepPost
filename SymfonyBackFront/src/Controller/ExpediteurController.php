<?php

namespace App\Controller;

use App\ClassesOutils\FormatageObjet;
use App\Entity\Expediteur;
use App\Form\ExpediteurType;
use App\Repository\ExpediteurRepository;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
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
Cette classe permet de créer, modifier, activer et supprimer un expéditeur.
*/

#[Route('/', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class ExpediteurController extends AbstractController
{
    #[Route('/expediteurs', name: 'expediteur')]
    public function index(
        ExpediteurRepository $expediteurs,
        Request $request,
        PaginatorInterface $paginator
    ): Response {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $rechercheExpediteur = $request->get('recherche');
        $isCheckBoxExact = $request->get('checkBoxExact');
        $openDetails = $request->get('openDetails') ?? false;

        if ($rechercheExpediteur != null && strval($rechercheExpediteur)) {
            $isCheckBoxExact ? $data = $expediteurs->findBy(['nom' => $rechercheExpediteur])
                : $data = $expediteurs->findLike($rechercheExpediteur);
        } else {
            $data = $expediteurs->findAll([], ['id' => 'DESC']);
        }

        $expediteur = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            8
        );

        $expediteursInactifs = $expediteurs->findAllInactive(); // faire une requête perso SQL pour selectionner tous les expediteurs inactifs

        return $this->render('expediteur/index.html.twig', [
            'expediteurs' => $expediteur,
            'expediteursInactifs' => $expediteursInactifs,
            'isSearch' => $rechercheExpediteur,
            'openDetails' => $openDetails,
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false,
            'nbExpediteursTotal' => count($data)
        ]);
    }



    #[Route('/ajouter', name: 'addExpediteur')]
    public function new(Request $request, MailerInterface $mailer, ExpediteurRepository $expediteurRepo): Response
    {
        $serializer = new Serializer([new ObjectNormalizer()]);
        $form = $this->createForm(ExpediteurType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $expediteur = $form->getData();
            $expediteur->setClient(null);
            $expediteurArray = (new FormatageObjet)
                ->stringToLowerObject(
                    $expediteur,
                    Expediteur::class,
                    array('client', 'createdAt', 'updatedAt'),
                    true
                );
            $nbHeureExp = 24;
            $token = (new JWT())->encode(
                $expediteurArray,
                '8733c931dfe34198e060c1e7dae3a7f20887b00937859bba724a68b7de44f512', // pass phrase
                'HS256', // protocole d'encodage
                head: ['exp' => time() + (3600 * $nbHeureExp)]
            );
            $expInHtml = $nbHeureExp == 1 ? " heure </p>" : " heures </p>";
            $body = "
            <p> Bonjour" . ($form->get('prenom')->getData() != null ? " " . $form->get('prenom')->getData() . ",</p>" : ",</p>") . "<p>veuillez confirmer la création de votre compte client associé à l'email " . $form->get('email')->getData() . " avec le bouton se trouvant ci-dessous. </p>
            <p><a href='http://localhost:4200/profil/validation-nouveau-compte?token=" . $token . "'> Confirmer la création de mon compte client </a></p>
            <p> La confirmation va expirer dans " . $nbHeureExp . $expInHtml;


            try {
                $expediteur = $serializer->denormalize($expediteurArray, Expediteur::class);
                $expediteur->setCreatedAt(new DateTime('now'))->setUpdatedAt(new DateTime('now'))->setClient(null)->setRoles(['ROLE_INACTIF'])->setPassword(' ');
                $expediteurRepo->add($expediteur->setClient($form->get('client')->getData()), true);
            } catch (UniqueConstraintViolationException) {
                return $this->redirectToRoute('app_addExpediteur', [
                    'errorMessage' => "L'adresse mail saisie est déjà associé à un compte expéditeur",
                    'isError' => true
                ]);
            }

            try {
                $mail = (new Email())
                    ->from('step.automaticmailservice@gmail.com') // adresse de l'expéditeur de l'email ayant son email de configuré dans le .env
                    ->to($form->get('email')->getData())
                    ->subject('Création de votre compte client')
                    ->html($body);
                $mailer->send($mail);
                return $this->redirectToRoute('app_expediteur', [
                    'errorMessage' => "L'expéditeur "  . $expediteur->getNom() . " " . ($expediteur->getPrenom() ?? null) .  " a été créé"
                ]);
            } catch (TransportExceptionInterface $e) {
                $errorHandler = "Une erreur s'est produite lors de l'envoi du mail";
                // supprimer le compte expéditeur créé pendant l'envoi raté de l'email
                return $this->redirectToRoute('app_addExpediteur', [
                    'errorMessage' => $errorHandler ?? null,
                    'isError' => true
                ]);
            }
        }

        return $this->renderForm('expediteur/new.html.twig', [
            'form' => $form,
            'expediteursInactifs' => $expediteurRepo->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    #[Route('/edit/{id}', name: 'editExpediteur')]
    public function edit(Request $request, Expediteur $ancienExpediteur, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ExpediteurType::class, $ancienExpediteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ancienExpediteur->setClient(null);
            $expediteur = (new FormatageObjet)->stringToLowerObject(
                $ancienExpediteur,
                Expediteur::class,
                array('client')
            );
            try {
                $em->persist($expediteur->setClient($form->get('client')->getData()));
                $em->flush();
                return $this->redirectToRoute('app_expediteur', ['errorMessage' => "L'expéditeur " . ($form->get('nom')->getData() ?? null) . " " . $form->get('prenom')->getData() . " a été modifié"], Response::HTTP_SEE_OTHER);
            } catch (Exception $e) {
                return $this->redirectToRoute('app_editExpediteur', ['errorMessage' => "La modification de l'expediteur " . ($form->get('nom')->getData() ?? null) . " " . $form->get('prenom')->getData() . " est impossible car l'adresse mail est déjà attribuée à un autre expéditeur", 'isError' => true], Response::HTTP_SEE_OTHER);
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


    #[Route('/delete/{id}', name: 'deleteExpediteur', methods: ['POST'])]
    public function Delete(Expediteur $expediteur, ExpediteurRepository $expediteurRepository): Response
    {
        try {
            $expediteurRepository->remove($expediteur);
            return $this->redirectToRoute('app_expediteur', ['errorMessage' => "L'expéditeur " . $expediteur->getNom() . " " . ($expediteur->getPrenom() ?? null) . " a bien été supprimé", Response::HTTP_SEE_OTHER]);
        } catch (Exception) {
            return $this->redirectToRoute('app_expediteur', ['errorMessage' => "L'expéditeur " . $expediteur->getNom() . " " . ($expediteur->getPrenom() ?? null) . " n'a pas pu être supprimé.", 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/detailsExpediteur', name: 'detailsExpediteur')]
    public function Details(Request $request, ExpediteurRepository $expediteurRepository): Response
    {
        $expediteurId = $request->get('expediteurId');
        $expediteur = $expediteurRepository->find($expediteurId);
        return $this->render('expediteur/details.html.twig', [
            'expediteur' => $expediteur,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    #[Route('/activer', name: 'activateExpediteur')]
    public function Activate(Request $request, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em, MailerInterface $mailer): RedirectResponse
    {
        $expediteurId = $request->get('expediteurId');
        $expediteur = ($expediteurRepository->find($expediteurId))->setRoles(['ROLE_CLIENT']);
        $email = (new Email())
            ->from('step.automaticmailservice@gmail.com')
            ->subject('Activation de votre compte Step Post')
            ->to($expediteur->getEmail())
            ->text("Votre compte associé à l'adresse mail " . $expediteur->getEmail() . " a été activé. Vous pouvez donc vous connecter.");

        try {
            $em->persist($expediteur);
            $em->flush();
            $mailer->send($email);
            return $this->redirectToRoute('app_expediteur', ['errorMessage' => "L'expéditeur " . $expediteur->getNom() . " " . ($expediteur->getPrenom() ?? null) . " a bien été activé"]);
        } catch (UniqueConstraintViolationException $e) {
            return $this->redirectToRoute('app_expediteur', ['errorMessage' => "L'activation de l'expéditeur "  . $expediteur->getNom() . " " . ($expediteur->getPrenom() ?? null) .  " n'a pas pu être effectué", 'isError' => true]);
        }
    }
}
