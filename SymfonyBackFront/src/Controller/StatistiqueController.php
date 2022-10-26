<?php

namespace App\Controller;

use App\Repository\ExpediteurRepository;
use App\Repository\StatutCourrierRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/statistiques', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class StatistiqueController extends AbstractController
{
    #[Route('/', name: 'statistiques')]
    public function index(Request $request, ExpediteurRepository $expediteurRepository, ChartBuilderInterface $chartBuilderInterface, StatutCourrierRepository $statutCourrierRepository): Response
    {
        $nbHours = $request->get('nbHours') ?? 24;
        $nbExpediteurActifLast = count($expediteurRepository->FindExpediteurLastHours($nbHours, 'ROLE_CLIENT')); // Le nombre d'expéditeurs inscrits et actifs ces dernières x heures
        $nbExpediteurInactifLast = count($expediteurRepository->FindExpediteurLastHours($nbHours, 'ROLE_INACTIF')); // Le nombre d'expéditeurs inscrits et inactifs ces dernières x heures
        $nbCourriersImpressionLast = count($statutCourrierRepository->FindCourrierImpressionLastHours($nbHours)); // Le nombre de bordereaux des courriers/colis imprimé ces dernières x heures
        $nbCourriersEnvoiLast = count($statutCourrierRepository->FindCourrierEnvoiLastHours($nbHours)); // Le nombre de courriers/colis envoyés ces dernières x heures

        $courrierStatutsChart = $chartBuilderInterface->createChart(Chart::TYPE_DOUGHNUT);
        $courrierStatutsChart->setData([
            'labels' => ["en attente", "pris en charge", "mis en instance", "avisé", "distribué", "NPAI", "non réclamé"],
            'datasets' => [
                [
                    'data' => [
                        count($statutCourrierRepository->FindCourriersByLastStatut(1)),
                        count($statutCourrierRepository->FindCourriersByLastStatut(2)),
                        count($statutCourrierRepository->FindCourriersByLastStatut(3)),
                        count($statutCourrierRepository->FindCourriersByLastStatut(4)),
                        count($statutCourrierRepository->FindCourriersByLastStatut(5)),
                        count($statutCourrierRepository->FindCourriersByLastStatut(6)),
                        count($statutCourrierRepository->FindCourriersByLastStatut(7))
                    ],
                    'backgroundColor' => [
                        'rgb(250, 220, 3)',
                        'rgb(43, 222, 211)',
                        'rgb(16, 36, 200)',
                        'rgb(99, 67, 175)',
                        'rgb(36, 166, 64)',
                        'rgb(238, 155, 49)',
                        'rgb(193, 52, 21)'
                    ]
                ]
            ]
        ]);

        $courrierStatutsChart->setOptions([
            'plugins' => [
                'legend' => [
                    'display' => false
                ]
            ]
        ]);


        return $this->render('statistique/index.html.twig', [
            'errorMessage' => null,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'chart' => $courrierStatutsChart,
            'nbExpediteurActifLast' => $nbExpediteurActifLast,
            'nbExpediteurInactifLast' => $nbExpediteurInactifLast,
            'nbCourriersImpressionLast' => $nbCourriersImpressionLast,
            'nbCourriersEnvoiLast' => $nbCourriersEnvoiLast
        ]);
    }
}
