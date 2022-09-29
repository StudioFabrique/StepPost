<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use App\Repository\ExpediteurRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
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
    public function ShowRaisonsSociales(ClientRepository $clientRepository, ExpediteurRepository $expediteurRepository): Response
    {
        $raisonsSociales = $clientRepository->findAll();
        return $this->render('raisonSociale/raisonSociale.html.twig', [
            'raisonsSociales' => $raisonsSociales,
            'expediteursInactifs' => $expediteurRepository->findAllInactive()
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
            'expediteursInactifs' => $expediteurRepository->findAllInactive()
        ]);
    }

    #[Route('/ajouterRaisonSociale', name: 'addRaisonSociale')]
    public function AddRaisonSociale(Request $request, ClientRepository $clientRepository, ExpediteurRepository $expediteurRepository): Response
    {
        // uniqueConstraint est une requête obtenue lorsque la raison sociale ajoutée existe déjà.
        // L'opérateur ternaire (?:) utilisé permet de ne pas afficher l'erreur lors du premier rendu
        // du formulaire. 
        $uniqueConstraint = $request->get('uniqueConstraint') != null ? $request->get('uniqueConstraint') : 'no error';

        $raisonSociale = new Client();
        $form = ($this->createForm(ClientType::class, $raisonSociale))->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $raisonSociale->setRaisonSociale(strip_tags(strtolower($form->get('raisonSociale')->getData())));
                $clientRepository->add($raisonSociale, true);
                return $this->redirectToRoute('app_raisonSociale', []);
            } catch (UniqueConstraintViolationException $e) {
                return $this->redirectToRoute('addRaisonSociale', [
                    'uniqueConstraint' => 'La raison sociale existe déjà.'
                ]);
            }
        }

        return $this->renderForm('raisonSociale/newRaisonSociale.html.twig', [
            'uniqueConstraint' => $uniqueConstraint,
            'form' => $form,
            'expediteursInactifs' => $expediteurRepository->findAllInactive()
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
            $em->persist($raison);
            $em->flush();
            return $this->redirectToRoute('app_raisonSociale');
        }

        return $this->renderForm('raisonSociale/editRaisonSociale.html.twig', [
            'form' => $form,
            'expediteursInactifs' => $expediteurRepository->findAllInactive()
        ]);
    }

    #[Route('/supprimerRaisonSociale', name: 'deleteRaisonSociale')]
    public function RemoveRaisonSociale(ClientRepository $clientRepository, Request $request): RedirectResponse
    {
        $raisonId = $request->get('raisonId');
        $raison = $clientRepository->find($raisonId);
        $clientRepository->remove($raison, true);
        return $this->redirectToRoute('app_raisonSociale'); // ajouter la suppression des raisons sociales de chaques expediteurs avec confirmation.
    }

    #[Route('/detacherClient', name: 'deleteClientRaisonSociale')]
    public function DeleteClientFrom(Request $request, ClientRepository $clientRepository, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em): RedirectResponse
    {
        $expediteurId = $request->get('expediteurId');
        $raisonId = $request->get('raisonId');
        $expediteur = $expediteurRepository->find($expediteurId);
        $em->persist($clientRepository->find($raisonId)->removeExpediteur($expediteur));
        $em->flush();
        return $this->redirectToRoute('app_clientsRaisonSociale', [
            'raisonId' => $raisonId
        ]);
    }

    #[Route('/ajouterClient', name: 'addClientRaisonSociale')]
    public function AddClientFrom(Request $request, ExpediteurRepository $expediteurRepository): Response
    {
        $raisonId = $request->get('raisonId');
        $clients = $expediteurRepository->findAllWithoutClient();
        return $this->render('raisonSociale/addClientsRaisonSociale.html.twig', [
            'clients' => $clients,
            'raisonId' => $raisonId,
            'expediteursInactifs' => $expediteurRepository->findAllInactive()
        ]);
    }

    #[Route('/ajouterLeClient', name: 'addTheClientRaisonSociale')]
    public function AddTheClientFrom(Request $request, ExpediteurRepository $expediteurRepository, ClientRepository $clientRepository, EntityManagerInterface $em): RedirectResponse
    {
        $raisonId = $request->get('raisonId');
        $expediteurId = $request->get('expediteurId');
        $em->persist($expediteurRepository->find($expediteurId)->setClient($clientRepository->find($raisonId)));
        $em->flush();
        return $this->redirectToRoute('app_clientsRaisonSociale', [
            'raisonId' => $raisonId,
            'expediteursInactifs' => $expediteurRepository->findAllInactive()
        ]);
    }
}
