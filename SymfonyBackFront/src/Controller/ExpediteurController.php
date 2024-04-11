<?php

namespace App\Controller;

use App\Entity\Expediteur;
use App\Form\ExpediteurType;
use App\Repository\ExpediteurRepository;
use App\Services\DataFinderService;
use App\Services\DateMakerService;
use App\Services\EntityManagementService;
use App\Services\FormattingService;
use App\Services\FormVerificationService;
use App\Services\MessageService;
use App\Services\MailService;
use App\Services\RequestManagerService;
use App\Services\TokenManagerService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Cette classe donne la possibilité de créer, modifier, activer et supprimer un expéditeur.
 */

#[Route('/', name: 'app_')]
#[IsGranted('ROLE_GESTION')]
class ExpediteurController extends AbstractController
{
    private $requestManagerService, $dataFinderService, $formattingService, $messageService, $formVerificationService, $entityManagementService, $tokenManagerService, $dateMakerService;

    /**
     * Constructeur
     */
    public function __construct(
        RequestManagerService $requestManagerService,
        DataFinderService $dataFinderService,
        FormattingService $formattingService,
        MessageService $messageService,
        FormVerificationService $formVerificationService,
        EntityManagementService $entityManagementService,
        TokenManagerService $tokenManagerService,
        DateMakerService $dateMakerService
    ) {
        $this->requestManagerService = $requestManagerService;
        $this->dataFinderService = $dataFinderService;
        $this->formattingService = $formattingService;
        $this->messageService = $messageService;
        $this->formVerificationService = $formVerificationService;
        $this->entityManagementService = $entityManagementService;
        $this->tokenManagerService = $tokenManagerService;
        $this->dateMakerService = $dateMakerService;
    }

    /**
     * Retourne un template twig avec la liste de tous les expéditeurs
     * @param Request $request
     */
    #[Route('/expediteurs', name: 'expediteur')]
    public function index(
        Request $request
    ): Response {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $data = $this->dataFinderService->GetExpediteurs($request);

        $dataPagination = $this->dataFinderService->Paginate($data, $request);

        return $this->render('expediteur/index.html.twig', $this->requestManagerService->GenerateRenderRequest("expediteur", $request, $dataPagination, $data));
    }

    /**
     * Crée un expéditeur inactif et lui envoi un lien de confirmation par mail afin de configurer son mot de passe.
     * @param Request $request
     */
    #[Route('/ajouter', name: 'addExpediteur')]
    public function new(Request $request, MailService $mailService): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if (count($this->dataFinderService->getRaisonSocialActive()) < 1) {
            return $this->redirectToRoute('app_expediteur', $this->messageService->GetErrorMessage("Expéditeur", 6));
        }

        $form = $this->createForm(ExpediteurType::class, null, ['type' => 'create']);
        $form->handleRequest($request);
        
        
        if ($form->isSubmitted() && $form->isValid()) {

            // vérification du code postal et numéro téléphone
            try {
                $this->formVerificationService->verifyField($form, 'add');
            } catch (Exception $e) {
                return $this->redirectToRoute('app_addExpediteur', [
                    'errorMessage' => $e->getMessage(),
                    'isError' => true
                ]);
            }

            try {
                $expediteurArray = $this->entityManagementService->MakeExpediteur($form);
            } catch (UniqueConstraintViolationException) {
                return $this->redirectToRoute('app_addExpediteur', $this->messageService->GetErrorMessage("Expéditeur", 1));
            }

            try {
                $mailService->sendMail($this->tokenManagerService->generateToken($expediteurArray, 24), 24, $form);
                return $this->redirectToRoute('app_expediteur', $this->messageService->GetSuccessMessage("Expéditeur", 1));
            } catch (TransportExceptionInterface $e) {
                // supprimer le compte expéditeur créé si envoi raté de l'email
                return $this->redirectToRoute('app_addExpediteur', $this->messageService->GetErrorMessage("Expéditeur", 2));
            }
        }

        return $this->renderForm('expediteur/new.html.twig', $this->requestManagerService->GenerateRenderFormRequest('expediteur', $request, $form));
    }

    /**
     * Modifie les informations d'un expéditeur
     */
    #[Route('/edit/{id}', name: 'editExpediteur')]
    public function edit(Request $request, Expediteur $ancienExpediteur, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if (count($this->dataFinderService->getRaisonSocialActive()) < 1) {
            return $this->redirectToRoute('app_expediteur', $this->messageService->GetErrorMessage("Expéditeur", 6));
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
            } catch (Exception $e) {
                return $this->redirectToRoute('app_editExpediteur', [
                    'errorMessage' => $e->getMessage(),
                    'isError' => true,
                    'id' => $ancienExpediteur->getId()
                ]);
            }
            $ancienExpediteur->setClient(null);
            try {
                $expediteur = $this->formattingService->stringToLowerObject(
                    $ancienExpediteur,
                    Expediteur::class,
                    array('client', 'password')
                );
                $em->persist($expediteur->setUpdatedAt($this->dateMakerService->createFromDateTimeZone())->setClient($form->get('addClient')->getData())->setPassword($ancienExpediteur->getPassword()));
                $em->flush();
                return $this->redirectToRoute('app_expediteur', ['errorMessage' => $this->messageService->GetErrorMessage('Expéditeur', 3, $expediteur->getNom())], Response::HTTP_SEE_OTHER);
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

    /**
     * Supprime un expéditeur
     */
    #[Route('/delete', name: 'deleteExpediteur')]
    public function Delete(Request $request, EntityManagerInterface $em, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Expéditeur"]["Suppression"];
        $messageErreur = $messages["Messages Erreurs"]["Expéditeur"]["Suppression"];

        try {
            $expediteur = $expediteurRepository->find($request->get('id'));
            $request->get("mode") == "temp"
                ? $em->persist($expediteur->setRoles(["ROLE_DELETED"]))
                : $expediteurRepository->remove($expediteur, false);
            $em->flush();
            return $this->redirectToRoute('app_expediteur', ['errorMessage' => str_replace('[nom]', $expediteur->getNom(), $message), Response::HTTP_SEE_OTHER]);
        } catch (Exception) {
            return $this->redirectToRoute('app_expediteur', ['errorMessage' => str_replace('[nom]', $expediteur->getNom(), $messageErreur), 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }

    /**
     * Change le rôle d'un expéditeur à ROLE_CLIENT
     */
    #[Route('/activer', name: 'activateExpediteur')]
    public function Activate(Request $request, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if (count($this->dataFinderService->getRaisonSocialActive()) < 1) {
            return $this->redirectToRoute('app_expediteur', $this->messageService->GetErrorMessage("Expéditeur", 6));
        }

        $expediteurId = $request->get('expediteurId');
        $expediteur = $expediteurRepository->find($expediteurId);
        $client = $expediteur->getClient();
        if (str_contains($client->getRaisonSociale(), 'tmp_')) {
            $form = $this->createForm(ExpediteurType::class, $expediteur, ['type' => 'editRaison', 'nom' => strtoupper($expediteur->getNom()) . ' ' . $expediteur->getPrenom()])->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $expediteurRepository->add($expediteur->setClient($form->get('addClient')->getData())->setRoles(['ROLE_CLIENT']), true);
            }
            return $this->renderForm('expediteur/edit.html.twig', [
                'form' => $form,
                'errorMessage' => null,
                'expediteursInactifs' => null
            ]);
        }
        $client->setRaisonSociale(str_replace("tmp_", "", $client->getRaisonSociale()));
        $em->persist($client);
        $email = (new Email())
            ->from('steppost64@gmail.com')
            ->subject('Activation de votre compte Step Post')
            ->to($expediteur->getEmail())
            ->html("<p>Votre compte associé à l'adresse mail " . $expediteur->getEmail() . " a été activé. Vous pouvez donc vous connecter à l'adresse : </p><a href='https://step-post.fr'>https://step-post.fr</a>");

        try {
            $em->persist($expediteur->setUpdatedAt($this->dateMakerService->createFromDateTimeZone())->setRoles(['ROLE_CLIENT']));
            $em->flush();
            $mailer->send($email);
            return $this->redirectToRoute('app_expediteur', $this->messageService->GetSuccessMessage('Expéditeur', 4, ''));
        } catch (Exception $e) {
            return $this->redirectToRoute('app_expediteur', $this->messageService->GetErrorMessage('Expéditeur', 5, ''));
        }
    }
}
