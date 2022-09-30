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
    #[Route('/', name: 'raisonSociale')]
    public function ShowRaisonsSociales(ClientRepository $clientRepository, ExpediteurRepository $expediteurRepository, Request $request): Response
    {
        $raisonsSociales = $clientRepository->findAll();
        return $this->render('raisonSociale/raisonSociale.html.twig', [
            'raisonsSociales' => $raisonsSociales,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    #[Route('/RaisonSocialeClients', name: 'clientsRaisonSociale')]
    public function ShowClientsRaisonsSociales(ClientRepository $clientRepository, Request $request, ExpediteurRepository $expediteurRepository): Response
    {
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
        $raisonSociale = new Client();
        $form = ($this->createForm(ClientType::class, $raisonSociale))->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $raisonSociale->setRaisonSociale(strip_tags(strtolower($form->get('raisonSociale')->getData())));
                $clientRepository->add($raisonSociale, true);
                return $this->redirectToRoute('app_raisonSociale', ['errorMessage' => 'La raison sociale a bien été créé']);
            } catch (UniqueConstraintViolationException $e) {
                return $this->redirectToRoute('app_addRaisonSociale', ['errorMessage' => 'La raison sociale saisie existe déjà', 'isError' => true]);
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
        $em = $manager->getManager();
        $raisonId = $request->get('raisonId');
        $raison = $clientRepository->find($raisonId);
        $form = ($this->createForm(ClientType::class, $raison))->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $raison = $form->getData();
            $raison->SetRaisonSociale((strip_tags(strtolower($form->get('raisonSociale')->getData()))));
            try {
                $em->persist($raison);
                $em->flush();
                return $this->redirectToRoute('app_raisonSociale', ['errorMessage' => 'La raison sociale ' . $form->get('raisonSociale') . ' a bien été modifié']);
            } catch (Exception) {
                return $this->redirectToRoute('app_editRaisonSociale', ['errorMessage' => 'La modification de la raison sociale a échoué', 'isError' => true]);
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
        $raisonId = $request->get('raisonId');
        $raison = $clientRepository->find($raisonId);

        try {
            $clientRepository->remove($raison, true);
            return $this->redirectToRoute('app_raisonSociale', ['errorMessage' => 'La raison sociale ' . $raison->getRaisonSociale() . ' a bien été supprimé']);
        } catch (Exception) {
            return $this->redirectToRoute('app_deleteRaisonSociale', ['errorMessage' => 'La suppression de la raison sociale ' . $raison->getRaisonSociale() . ' a échoué']);
        }
    }

    #[Route('/detacherClient', name: 'deleteClientRaisonSociale')]
    public function DeleteClientFrom(Request $request, ClientRepository $clientRepository, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em): RedirectResponse
    {
        $expediteurId = $request->get('expediteurId');
        $raisonId = $request->get('raisonId');
        $expediteur = $expediteurRepository->find($expediteurId);

        try {
            $em->persist($clientRepository->find($raisonId)->removeExpediteur($expediteur));
            $em->flush();
            return $this->redirectToRoute('app_clientsRaisonSociale', [
                'raisonId' => $raisonId,
                'errorMessage' => "L'expéditeur " . ($expediteur->getPrenom() ?? null) . " " . $expediteur->getNom() . " a bien été détaché de cette raison sociale"
            ]);
        } catch (Exception) {
            return $this->redirectToRoute('app_clientsRaisonSociale', [
                'raisonId' => $raisonId,
                'errorMessage' => "L'expéditeur " . ($expediteur->getPrenom() ?? null) . " " . $expediteur->getNom() . " n'a pas pu être détaché de cette raison sociale",
                'isError' => true
            ]);
        }
    }

    #[Route('/ajouterClient', name: 'addClientRaisonSociale')]
    public function AddClientFrom(Request $request, ExpediteurRepository $expediteurRepository): Response
    {
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
        $raisonId = $request->get('raisonId');
        $expediteurId = $request->get('expediteurId');
        $expediteur = $expediteurRepository->find($expediteurId);

        try {
            $em->persist($expediteur->setClient($clientRepository->find($raisonId)));
            $em->flush();
            return $this->redirectToRoute('app_clientsRaisonSociale', [
                'raisonId' => $raisonId,
                'expediteursInactifs' => $expediteurRepository->findAllInactive(),
                'errorMessage' => "L'expéditeur " . ($expediteur->getPrenom() ?? null) . " " . $expediteur->getNom() . " a été ajouté à cette raison sociale"
            ]);
        } catch (Exception) {
            return $this->redirectToRoute('app_clientsRaisonSociale', [
                'raisonId' => $raisonId,
                'expediteursInactifs' => $expediteurRepository->findAllInactive(),
                'errorMessage' => "L'expéditeur " . ($expediteur->getPrenom() ?? null) . " " . $expediteur->getNom() . " n'a pas pu être ajouté à cette raison sociale"
            ]);
        }
    }
}
