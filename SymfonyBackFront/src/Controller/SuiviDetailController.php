<?php

namespace App\Controller;

use App\Entity\StatutCourrier;
use App\Form\StatutCourrierType;
use App\Repository\CourrierRepository;
use App\Repository\StatutCourrierRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/suivi/detail', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class SuiviDetailController extends AbstractController
{
    #[Route('/ajouterdetail', name: 'statut_add')]
    public function Add(Request $request, StatutCourrierRepository $statutCourrierRepository, CourrierRepository $courrierRepository): Response
    {
        $courrierId = $request->get('id');
        $statutCourrier = new StatutCourrier();
        $form = $this->createForm(StatutCourrierType::class, $statutCourrier);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $statutCourrier->setCourrier($courrierRepository->find($courrierId));
            $statutCourrier->setDate(new DateTime('now'));
            $statutCourrierRepository->add($statutCourrier, true);
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('suivi_detail/add.html.twig', [
            'id' => $courrierId,
            'form' => $form
        ]);
    }
}
