<?php

namespace App\Controller;

use App\Entity\Statutcourrier;
use App\Form\StatutCourrierType;
use App\Repository\StatutcourrierRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/suivi/detail', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class SuiviDetailController extends AbstractController
{

    #[Route('/edit/{id}', name: 'status_edit')]
    public function edit(Request $request, StatutCourrier $statutCourrier, StatutCourrierRepository $statutsCourrier): Response
    {
        $form = $this->createForm(StatutCourrierType::class, $statutCourrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $statutsCourrier->add($statutCourrier);
            return $this->redirectToRoute('app_accueil', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('suivi_detail/edit.html.twig', [
            'statutsCourrier' => $statutsCourrier,
            'form' => $form,
        ]);
    }
}
