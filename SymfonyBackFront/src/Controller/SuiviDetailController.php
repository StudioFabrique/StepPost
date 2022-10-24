<?php

namespace App\Controller;

use App\Entity\Statut;
use App\Entity\StatutCourrier;
use App\Form\StatutCourrierType;
use App\Repository\CourrierRepository;
use App\Repository\ExpediteurRepository;
use App\Repository\StatutCourrierRepository;
use App\Repository\StatutRepository;
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

    public function Update(Request $request, StatutCourrierRepository $statutCourrierRepository, StatutRepository $statutRepository, CourrierRepository $courrierRepository): Response
    {
        $courrierId = $request->get('id');
        $statuts = $courrierRepository->find($courrierId)->getStatutsCourrier();

        $lastStatutId = 0;
        foreach ($statuts as $statut) {
            $lastStatutId = $statut->getStatut()->getId() <= $lastStatutId ? $lastStatutId : $statut->getStatut()->getId();
            $facteur = $statut->getFacteur();
        }

        if ($lastStatutId + 1 <= 7) {
            $statut = $statutRepository->find($lastStatutId + 1);
        } else {
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => 'Le statut ne peut plus être mis à jour', 'isError' => true], Response::HTTP_SEE_OTHER);
        }

        $statutCourrier = new StatutCourrier();
        $statutCourrier
            ->setCourrier($courrierRepository->find($courrierId))
            ->setStatut($statut)
            ->setDate(new DateTime('now'))
            ->setFacteur($facteur);
        try {
            $statutCourrierRepository->add($statutCourrier, true);
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => 'Le statut a été mis à jour'], Response::HTTP_SEE_OTHER);
        } catch (Exception) {
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => 'Impossible mettre le statut à jour', 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }
}
