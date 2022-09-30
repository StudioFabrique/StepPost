<?php

namespace App\Controller;

use App\Entity\StatutCourrier;
use App\Form\StatutCourrierType;
use App\Repository\CourrierRepository;
use App\Repository\ExpediteurRepository;
use App\Repository\StatutCourrierRepository;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/suivi/detail', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class SuiviDetailController extends AbstractController
{
    #[Route('/mettreAjourStatut', name: 'statut_add')]
    public function Add(Request $request, StatutCourrierRepository $statutCourrierRepository, CourrierRepository $courrierRepository, ExpediteurRepository $expediteurRepository): Response
    {
        $courrierId = $request->get('id');
        $statutCourrier = new StatutCourrier();
        $form = $this->createForm(StatutCourrierType::class, $statutCourrier);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $statutCourrier->setCourrier($courrierRepository->find($courrierId));
            $statutCourrier->setDate(new DateTime('now'));
            try {
                $statutCourrierRepository->add($statutCourrier, true);
                return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => 'Le statut a été mis à jour'], Response::HTTP_SEE_OTHER);
            } catch (Exception) {
                return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => 'Le statut a été mis à jour', 'isError' => true], Response::HTTP_SEE_OTHER);
            }
        }
        return $this->renderForm('suivi_detail/add.html.twig', [
            'id' => $courrierId,
            'form' => $form,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage'),
            'isError' => $request->get('isError') ?? false
        ]);
    }
}
