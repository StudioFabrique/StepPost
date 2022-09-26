<?php

namespace App\Controller;

use App\Entity\StatutCourrier;
use App\Form\StatutCourrierType;
use App\Repository\StatutCourrierRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/suivi/detail', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class SuiviDetailController extends AbstractController
{

    #[Route('/modifier/{id}', name: 'statut_edit')]
    public function Edit(Request $request, StatutCourrier $statutCourrier, StatutCourrierRepository $statutsCourrier): Response
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

    #[Route('/supprimerSuivi', 'statut_remove')]
    public function Delete(Request $request, StatutCourrierRepository $statutsCourrier): RedirectResponse
    {
        $statutCourrier = $statutsCourrier->find($request->get('statutCourrierId'));

        $statutsCourrier->remove($statutCourrier, true);

        return $this->redirectToRoute('app_accueil');
    }
}
