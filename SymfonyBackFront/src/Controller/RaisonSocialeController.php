<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use App\Repository\ExpediteurRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

#[Route('/RaisonSociale', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class RaisonSocialeController extends AbstractController
{
    /* 
        Retourne un template twig avec la liste de toutes les raisons sociales
    */
    #[Route('/', name: 'raisonSociale')]
    public function ShowRaisonsSociales(PaginatorInterface $paginator, ClientRepository $clientRepository, ExpediteurRepository $expediteurRepository, Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $currentPage = $request->get('currentPage') ?? 1;
        $data = $clientRepository->findActiveClients();
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
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'currentPage' => $request->query->getInt('page') > 1 ? $request->query->getInt('page') <= 2 : $currentPage,
            'isError' => $request->get('isError') ?? false,
            'nbRaisonsTotal' => count($data)
        ]);
    }

    #[Route('/RaisonSocialeClients', name: 'clientsRaisonSociale')]
    public function ShowClientsRaisonsSociales(ClientRepository $clientRepository, Request $request, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $raisonId = $request->get('raisonId');
        $raison = $clientRepository->find($raisonId);
        $clients = $raison->getExpediteurs();
        return $this->render('raisonSociale/clientsRaisonSociale.html.twig', [
            'raison' => $raison,
            'clients' => $clients,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    #[Route('/ajouterRaisonSociale', name: 'addRaisonSociale')]
    public function AddRaisonSociale(Request $request, ClientRepository $clientRepository, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Raison sociale"]["Création"];
        $messageErreur = $messages["Messages Erreurs"]["Raison sociale"]["Création"];

        $raisonSociale = new Client();
        $form = ($this->createForm(ClientType::class, $raisonSociale))->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $name = $form->get('raisonSociale')->getData();
                $raisonSociale->setRaisonSociale(strip_tags(strtolower($name)));
                foreach ($clientRepository->findAll() as $raison) {
                    if ($raison->getRaisonSociale() == str_replace([' ', 'tmp_'], '', $name)) {
                        throw new Exception;
                    }
                }
                $clientRepository->add($raisonSociale, true);
                return $this->redirectToRoute('app_raisonSociale', ['errorMessage' => str_replace('[nom]', $raisonSociale->getRaisonSociale(), $message)]);
            } catch (Exception $e) {
                return $this->redirectToRoute('app_addRaisonSociale', ['errorMessage' => str_replace('[nom]', $raisonSociale->getRaisonSociale(), $messageErreur), 'isError' => true]);
            }
        }

        return $this->renderForm('raisonSociale/newRaisonSociale.html.twig', [
            'form' => $form,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    #[Route('/modifierRaisonSociale', name: 'editRaisonSociale')]
    public function EditRaisonSociale(ClientRepository $clientRepository, Request $request, ManagerRegistry $manager, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Raison sociale"]["Modification"];
        $messageErreur = $messages["Messages Erreurs"]["Raison sociale"]["Modification"];

        $em = $manager->getManager();
        $raisonId = $request->get('raisonId');
        $raisonSociale = $clientRepository->find($raisonId);
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
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    #[Route('/supprimerRaisonSociale', name: 'deleteRaisonSociale')]
    public function RemoveRaisonSociale(ClientRepository $clientRepository, Request $request): RedirectResponse
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Raison sociale"]["Suppression"];
        $messageErreur = $messages["Messages Erreurs"]["Raison sociale"]["Suppression"];

        $raisonId = $request->get('raisonId');
        $raisonSociale = $clientRepository->find($raisonId);

        if (count($raisonSociale->getExpediteurs()) > 0) {
            return $this->redirectToRoute('app_raisonSociale', ['errorMessage' => str_replace('[nom]', $raisonSociale->getRaisonSociale(), $messageErreur)]);
        }

        try {
            $clientRepository->remove($raisonSociale, true);
            return $this->redirectToRoute('app_raisonSociale', ['errorMessage' => str_replace('[nom]', $raisonSociale->getRaisonSociale(), $message)]);
        } catch (Exception) {
            return $this->redirectToRoute('app_raisonSociale', ['errorMessage' => str_replace('[nom]', $raisonSociale->getRaisonSociale(), $messageErreur)]);
        }
    }


    #[Route('/ajouterClient', name: 'addClientRaisonSociale')]
    public function AddClientFrom(Request $request, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $raisonId = $request->get('raisonId');
        $clients = $expediteurRepository->findAllWithoutClient();
        return $this->render('raisonSociale/addClientsRaisonSociale.html.twig', [
            'clients' => $clients,
            'raisonId' => $raisonId,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    #[Route('/ajouterLeClient', name: 'addTheClientRaisonSociale')]
    public function AddTheClientFrom(Request $request, ExpediteurRepository $expediteurRepository, ClientRepository $clientRepository, EntityManagerInterface $em): RedirectResponse
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Raison sociale"]["Ajout expéditeur"];
        $messageErreur = $messages["Messages Erreurs"]["Raison sociale"]["Ajout expéditeur"];

        $raisonId = $request->get('raisonId');
        $expediteurId = $request->get('expediteurId');
        $expediteur = $expediteurRepository->find($expediteurId);

        try {
            $em->persist($expediteur->setClient($clientRepository->find($raisonId)));
            $em->flush();
            return $this->redirectToRoute('app_addClientRaisonSociale', [
                'raisonId' => $raisonId,
                'expediteursInactifs' => $expediteurRepository->findAllInactive(),
                'errorMessage' => str_replace('[nom]', $expediteur->getNom(), $message)
            ]);
        } catch (Exception) {
            return $this->redirectToRoute('app_addClientRaisonSociale', [
                'raisonId' => $raisonId,
                'expediteursInactifs' => $expediteurRepository->findAllInactive(),
                'errorMessage' => str_replace('[nom]', $expediteur->getNom(), $messageErreur)
            ]);
        }
    }

    #[Route('/detacherClient', name: 'deleteClientRaisonSociale')]
    public function DeleteClientFrom(Request $request, ClientRepository $clientRepository, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em): RedirectResponse
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Raison sociale"]["Supression expéditeur"];
        $messageErreur = $messages["Messages Erreurs"]["Raison sociale"]["Supression expéditeur"];

        $expediteurId = $request->get('expediteurId');
        $raisonId = $request->get('raisonId');
        $expediteur = $expediteurRepository->find($expediteurId);

        try {
            $em->persist($clientRepository->find($raisonId)->removeExpediteur($expediteur));
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
