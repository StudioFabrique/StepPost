<?php

namespace App\Controller;

use App\Repository\ExpediteurRepository;
use App\Repository\StatutCourrierRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/statistiques', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class StatistiqueController extends AbstractController
{
    #[Route('/', name: 'statistiques')]
    public function index(ExpediteurRepository $expediteurRepository, ChartBuilderInterface $chartBuilderInterface, StatutCourrierRepository $statutCourrierRepository): Response
    {
        $nbExpediteurLast = count($expediteurRepository->findExpediteurLastHours(24));
        var_dump($nbExpediteurLast);

        $courrierStatutsChart = $chartBuilderInterface->createChart(Chart::TYPE_DOUGHNUT);
        $courrierStatutsChart->setData([
            'labels' => ["en attente", "pris en charge", "mis en instance", "avisé", "distribué", "NPAI", "retour à l'expéditeur"],
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
                        'rgb(27, 200, 16)',
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
            'chart' => $courrierStatutsChart
        ]);
    }
}
