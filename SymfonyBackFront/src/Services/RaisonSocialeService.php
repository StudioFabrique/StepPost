<?php

namespace App\Services;

use Exception;
use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use App\Repository\ExpediteurRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



class RaisonSocialeService extends AbstractController {

    private $clientRepository, $expediteurRepository, $manager;



    function __construct(ClientRepository $clientRepository, ExpediteurRepository $expediteurRepository, ManagerRegistry $manager){
        $this->clientRepository = $clientRepository;
        $this->expediteurRepository = $expediteurRepository;
        $this->manager = $manager;
    }

    function SocialReasonShow(Request $request, PaginatorInterface $paginator):Response{

        $currentPage = $request->get('currentPage') ?? 1;
        $data = $this->clientRepository->findActiveClients();
        // replace 'tmp_' if exist for clients
        foreach ($data as $client) {
            $client->setRaisonSociale(str_replace('tmp_', '', $client->getRaisonSociale()));
        }

        $raisonsSociales = $paginator->paginate(
            $data,
            $request->query->getInt('page') < 2 ? $currentPage : $request->query->getInt('page')
        );

        return $this->render('raisonSociale/raisonSociale.html.twig', [
            'raisonsSociales' => $raisonsSociales,
            'expediteursInactifs' => $this->expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'currentPage' => $request->query->getInt('page') > 1 ? $request->query->getInt('page') <= 2 : $currentPage,
            'isError' => $request->get('isError') ?? false,
            'nbRaisonsTotal' => count($data)
        ]);

    }

    function SocialReasonShowClient(Request $request):Response{

        $raisonId = $request->get('raisonId');
        $raison = $this->clientRepository->find($raisonId);
        $clients = $raison->getExpediteurs();
        return $this->render('raisonSociale/clientsRaisonSociale.html.twig', [
            'raison' => $raison,
            'clients' => $clients,
            'expediteursInactifs' => $this->expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);

    }

    function SocialReasonAdd(Request $request):Response{

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Raison sociale"]["1,Création"];
        $messageErreur = $messages["Messages Erreurs"]["Raison sociale"]["1,Création"];

        $raisonSociale = new Client();
        $form = ($this->createForm(ClientType::class, $raisonSociale))->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $name = $form->get('raisonSociale')->getData();
                $raisonSociale->setRaisonSociale(strip_tags(strtolower($name)));
                foreach ($this->clientRepository->findAll() as $raison) {
                    if ($raison->getRaisonSociale() == str_replace([' ', 'tmp_'], '', $name)) {
                        throw new Exception;
                    }
                }
                $this->clientRepository->add($raisonSociale, true);
                return $this->redirectToRoute('app_raisonSociale', ['errorMessage' => str_replace('[nom]', $raisonSociale->getRaisonSociale(), $message)]);
            } catch (Exception $e) {
                return $this->redirectToRoute('app_addRaisonSociale', ['errorMessage' => str_replace('[nom]', $raisonSociale->getRaisonSociale(), $messageErreur), 'isError' => true]);
            }
        }

        return $this->renderForm('raisonSociale/newRaisonSociale.html.twig', [
            'form' => $form,
            'expediteursInactifs' => $this->expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);

    }

    function SocialReasonEdit(Request $request):Response{

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Raison sociale"]["2,Modification"];
        $messageErreur = $messages["Messages Erreurs"]["Raison sociale"]["2,Modification"];

        $em = $this->manager->getManager();
        $raisonId = $request->get('raisonId');
        $raisonSociale = $this->clientRepository->find($raisonId);
        $form = ($this->createForm(ClientType::class, $raisonSociale))->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $raisonSociale = $form->getData();
            $raisonSociale->SetRaisonSociale((strip_tags(strtolower($form->get('raisonSociale')->getData()))));
            try {
                $em->persist($raisonSociale);
                $em->flush();
                return $this->redirectToRoute('app_raisonSociale', ['errorMessage' => str_replace('[nom]', $raisonSociale->getRaisonSociale(), $message)]);
            } catch (Exception) {
                return $this->redirectToRoute('app_editRaisonSociale', ['errorMessage' => str_replace('[nom]', $raisonSociale->getRaisonSociale(), $messageErreur), 'isError' => true]);
            }
        }

        return $this->renderForm('raisonSociale/editRaisonSociale.html.twig', [
            'form' => $form,
            'expediteursInactifs' => $this->expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);

    }

    function SocialReasonRemove(Request $request):RedirectResponse {

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Raison sociale"]["3,Suppression"];
        $messageErreur = $messages["Messages Erreurs"]["Raison sociale"]["3,Suppression"];

        $raisonId = $request->get('raisonId');
        $raisonSociale = $this->clientRepository->find($raisonId);

        if (count($raisonSociale->getExpediteurs()) > 0) {
            return $this->redirectToRoute('app_raisonSociale', ['errorMessage' => str_replace('[nom]', $raisonSociale->getRaisonSociale(), $messageErreur)]);
        }

        try {
            $this->clientRepository->remove($raisonSociale, true);
            return $this->redirectToRoute('app_raisonSociale', ['errorMessage' => str_replace('[nom]', $raisonSociale->getRaisonSociale(), $message)]);
        } catch (Exception) {
            return $this->redirectToRoute('app_raisonSociale', ['errorMessage' => str_replace('[nom]', $raisonSociale->getRaisonSociale(), $messageErreur)]);
        }
    }

    function SocialReasonClientFrom(Request $request):Response{

        $raisonId = $request->get('raisonId');
        $clients = $this->expediteurRepository->findAllWithoutClient();
        return $this->render('raisonSociale/addClientsRaisonSociale.html.twig', [
            'clients' => $clients,
            'raisonId' => $raisonId,
            'expediteursInactifs' => $this->expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    function SocialReasonTheCLientFrom(Request $request, EntityManagerInterface $em):RedirectResponse {

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Raison sociale"]["4,Ajout expéditeur"];
        $messageErreur = $messages["Messages Erreurs"]["Raison sociale"]["4,Ajout expéditeur"];

        $raisonId = $request->get('raisonId');
        $expediteurId = $request->get('expediteurId');
        $expediteur = $this->expediteurRepository->find($expediteurId);

        try {
            $em->persist($expediteur->setClient($this->clientRepository->find($raisonId)));
            $em->flush();
            return $this->redirectToRoute('app_addClientRaisonSociale', [
                'raisonId' => $raisonId,
                'expediteursInactifs' => $this->expediteurRepository->findAllInactive(),
                'errorMessage' => str_replace('[nom]', $expediteur->getNom(), $message)
            ]);
        } catch (Exception) {
            return $this->redirectToRoute('app_addClientRaisonSociale', [
                'raisonId' => $raisonId,
                'expediteursInactifs' => $this->expediteurRepository->findAllInactive(),
                'errorMessage' => str_replace('[nom]', $expediteur->getNom(), $messageErreur)
            ]);
        }
    }

    function SocialReasonDelteCLient(Request $request, EntityManager $em):RedirectResponse {

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Raison sociale"]["5,Supression expéditeur"];
        $messageErreur = $messages["Messages Erreurs"]["Raison sociale"]["5,Supression expéditeur"];

        $expediteurId = $request->get('expediteurId');
        $raisonId = $request->get('raisonId');
        $expediteur = $this->expediteurRepository->find($expediteurId);

        try {
            $em->persist($this->clientRepository->find($raisonId)->removeExpediteur($expediteur));
            $em->flush();
            return $this->redirectToRoute('app_clientsRaisonSociale', [
                'raisonId' => $raisonId,
                'errorMessage' => str_replace('[nom]', $expediteur->getNom(), $message)
            ]);
        } catch (Exception) {
            return $this->redirectToRoute('app_clientsRaisonSociale', [
                'raisonId' => $raisonId,
                'errorMessage' => str_replace('[nom]', $expediteur->getNom(), $messageErreur),
                'isError' => true
            ]);
        }
    }
}