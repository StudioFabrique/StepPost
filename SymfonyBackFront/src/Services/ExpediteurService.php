<?php
namespace App\Services;

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

class ExpediteurService extends AbstractController
{
    private $formVerificationService, $dataFinderService, $messageService, $entityManagementService, $tokenManagerService, $requestManagerService, $formattingService, $dateMakerService;

    function __construct(FormVerificationService $formVerificationService, 
                        DataFinderService $dataFinderService, 
                        MessageService $messageService, 
                        EntityManagementService $entityManagementService,
                        TokenManagerService $tokenManagerService,
                        RequestManagerService $requestManagerService,
                        FormattingService $formattingService,
                        DateMakerService $dateMakerService)
                    {
                        $this->formVerificationService = $formVerificationService;
                        $this->dataFinderService = $dataFinderService;
                        $this->messageService = $messageService;
                        $this->entityManagementService = $entityManagementService;
                        $this->tokenManagerService = $tokenManagerService;
                        $this->requestManagerService = $requestManagerService;
                        $this->formattingService = $formattingService;
                        $this->dateMakerService = $dateMakerService;
                    }

    
    /**
     * Crée un expéditeur inactif et lui envoi un lien de confirmation par mail afin de configurer son mot de passe.
     * @param Request $request
     */  

    function newExpediteurService(Request $request, MailService $mailService): Response{

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
    function editExpediteurService(Request $request, Expediteur $ancienExpediteur, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em):Response {
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
        $message = $messages["Messages Informations"]["Expéditeur"]["2,Modification"];
        $messageErreur = $messages["Messages Erreurs"]["Expéditeur"]["3,Modification"];

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
    
       /**
     * Supprime un expéditeur
     */

    function deleteExpediteurService(Request $request, EntityManagerInterface $em, ExpediteurRepository $expediteurRepository):Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Expéditeur"]["3,Suppression"];
        $messageErreur = $messages["Messages Erreurs"]["Expéditeur"]["4,Suppression"];

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

    function activateExpediteurService(Request $request, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em, MailerInterface $mailer):Response
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