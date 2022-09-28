<?php

namespace App\Controller;

use App\Entity\StatutCourrier;
use App\Form\StatutCourrierType;
use App\Repository\CourrierRepository;
use App\Repository\StatutCourrierRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
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
    #[Route('/ajouterdetail', name: 'statut_add')]
    public function Add(Request $request, StatutCourrierRepository $statutCourrierRepository, CourrierRepository $courrierRepository): Response
    {
        $courrierId = $request->get('id');
        $statutCourrier = new StatutCourrier();
        $form = $this->createForm(StatutCourrierType::class, $statutCourrier, ['SetDatetimeNow' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $statutCourrier->setCourrier($courrierRepository->find($courrierId));
            $statutCourrierRepository->add($statutCourrier, true);
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('suivi_detail/add.html.twig', [
            'id' => $courrierId,
            'form' => $form
        ]);
    }

    #[Route('/modifier/{id}', name: 'statut_edit')]
    public function Edit(Request $request, StatutCourrier $statutCourrier, StatutCourrierRepository $statutsCourrier, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(StatutCourrierType::class, $statutCourrier);
        $form->handleRequest($request);
        $courrierId = $statutCourrier->getCourrier()->getId();
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($statutCourrier);
            $em->flush();
            $courrierId = $statutCourrier->getCourrier()->getId();
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('suivi_detail/edit.html.twig', [
            'statutsCourrier' => $statutsCourrier,
            'form' => $form,
            'id' => $courrierId
        ]);
    }

    #[Route('/supprimerSuivi', 'statut_remove')]
    public function Delete(Request $request, StatutCourrierRepository $statutsCourrier): RedirectResponse
    {
        $statutCourrier = $statutsCourrier->find($request->get('id'));

        $courrierId = $statutCourrier->getCourrier()->getId();

        $statutsCourrier->remove($statutCourrier, true);

        return $this->redirectToRoute('app_suiviId', ['id' => $courrierId]);
    }
}
