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
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $nbHours = $request->get('nbHours') ?? 24;
        $nbExpediteurActifLast = count($expediteurRepository->FindExpediteurLastHours($nbHours, 'ROLE_CLIENT')); // Le nombre d'expéditeurs inscrits et actifs ces dernières x heures
        $nbExpediteurInactifLast = count($expediteurRepository->FindExpediteurLastHours($nbHours, 'ROLE_INACTIF')); // Le nombre d'expéditeurs inscrits et inactifs ces dernières x heures
        $nbCourriersImpressionLast = count($statutCourrierRepository->FindCourrierImpressionLastHours($nbHours)); // Le nombre de bordereaux des courriers/colis imprimé ces dernières x heures
        $nbCourriersEnvoiLast = count($statutCourrierRepository->FindCourrierEnvoiLastHours($nbHours)); // Le nombre de courriers/colis envoyés ces dernières x heures
        $nbCourriersRecuLast =  count($statutCourrierRepository->FindCourrierRecuLastHours($nbHours));

        /* 
            DOUGHNUT CHART
            Ce graphique réparti les différents nombres des derniers statuts des courriers
        */
        $courrierStatutsChart = ($chartBuilderInterface->createChart(Chart::TYPE_DOUGHNUT))
            ->setData([
                'labels' => ["en attente", "pris en charge", "avisé", "mis en instance", "distribué", "NPAI", "non réclamé"],
                'datasets' => [
                    [
                        'data' => [
                            count($statutCourrierRepository->findCourriersByLastStatut(1)),
                            count($statutCourrierRepository->findCourriersByLastStatut(2)),
                            count($statutCourrierRepository->findCourriersByLastStatut(3)),
                            count($statutCourrierRepository->findCourriersByLastStatut(4)),
                            count($statutCourrierRepository->findCourriersByLastStatut(5)),
                            count($statutCourrierRepository->findCourriersByLastStatut(6)),
                            count($statutCourrierRepository->findCourriersByLastStatut(7))
                        ],
                        'backgroundColor' => [
                            'rgb(255, 204, 64)',
                            'rgb(43, 222, 211)',
                            'rgb(16, 36, 200)',
                            'rgb(99, 67, 175)',
                            'rgb(36, 166, 64)',
                            'rgb(238, 155, 49)',
                            'rgb(193, 52, 21)'
                        ]
                    ]
                ]
            ])

            ->setOptions([
                'plugins' => [
                    'legend' => [
                        'display' => false
                    ]
                ]
            ]);

        /* 
            BAR CHART
            Ce graphique affiche le top 10 des facteurs ayant envoyé le plus de courriers avec le nombre de courrier associé
        */

        $labels = array();
        $data = array();
        foreach ($statutCourrierRepository->findTopFacteurs() as $facteur) {
            array_push($labels, $facteur["nom"]);
            array_push($data, $facteur["nombre_courriers"]);
        }


        $topFacteurChart = $chartBuilderInterface->createChart(Chart::TYPE_BAR)
            ->setData([
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => [
                            'rgb(255, 204, 64)',
                            'rgb(43, 222, 211)',
                            'rgb(16, 36, 200)',
                            'rgb(99, 67, 175)',
                            'rgb(36, 166, 64)',
                            'rgb(238, 155, 49)',
                            'rgb(193, 52, 21)'
                        ]
                    ]
                ]
            ])
            ->setOptions([
                'plugins' => [
                    'legend' => [
                        'display' => false
                    ]
                ]
            ]);

        return $this->render('statistique/index.html.twig', [
            'errorMessage' => null,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'nbExpediteurActifLast' => $nbExpediteurActifLast,
            'nbExpediteurInactifLast' => $nbExpediteurInactifLast,
            'nbCourriersImpressionLast' => $nbCourriersImpressionLast,
            'nbCourriersEnvoiLast' => $nbCourriersEnvoiLast,
            'nbCourriersRecuLast' => $nbCourriersRecuLast,
            'chart1' => $courrierStatutsChart,
            'chart2' => $topFacteurChart
        ]);
    }
}
