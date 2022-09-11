<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

#[Route('/client', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class RaisonSocialeController extends AbstractController
{
    #[Route('/RaisonsSociales', name: 'raisonSociale')]
    public function ShowRaisonsSociales(ClientRepository $clientRepository): Response
    {
        $raisonsSociales = $clientRepository->findAll();
        return $this->render('raisonSociale/raisonSociale.html.twig', [
            'raisonsSociales' => $raisonsSociales
        ]);
    }

    #[Route('/ajouterRaisonSociale', name: 'addRaisonSociale')]
    public function AddRaisonSociale(Request $request, ClientRepository $clientRepository): Response
    {
        // uniqueConstraint est une requête obtenue lorsque la raison sociale ajoutée existe déjà.
        // L'opérateur ternaire (?:) utilisé permet de ne pas afficher l'erreur lors du premier rendu
        // du formulaire. 
        $uniqueConstraint = $request->get('uniqueConstraint') != null ? $request->get('uniqueConstraint') : 'no error';

        $raisonSociale = new Client();
        $form = ($this->createForm(ClientType::class, $raisonSociale))->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $raisonSociale->setRaisonSociale($form->get('raisonSociale')->getData());
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
            'form' => $form
        ]);
    }
}
