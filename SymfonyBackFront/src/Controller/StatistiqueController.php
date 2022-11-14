<?php

namespace App\Controller;

use App\ClassesOutils\FormatageObjet;
use App\Repository\ExpediteurRepository;
use App\Repository\FacteurRepository;
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
    public function index(
        Request $request,
        ExpediteurRepository $expediteurRepository,
        ChartBuilderInterface $chartBuilderInterface,
        StatutCourrierRepository $statutCourrierRepository,
        FacteurRepository $facteurRepository
    ): Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $searchBar = $request->get('search') ?? null;

        if ($searchBar != null) {
            return $this->redirectToRoute('app_statistiques_facteur', ['facteur' => $searchBar]);
        }

        $nbHours = $request->get('nbHours') ?? 24;
        // Le nombre d'expéditeurs inscrits et actifs ces dernières x heures
        $nbCourriersImpression = count($statutCourrierRepository->FindCourrierImpression()); // Le nombre de bordereaux des courriers/colis imprimé ces dernières x heures
        $nbCourriersEnvoi = count($statutCourrierRepository->FindCourrierEnvoi()); // Le nombre de courriers/colis envoyés ces dernières x heures
        $nbCourriersRecu =  count($statutCourrierRepository->FindCourrierRecu());

        /* 
            DOUGHNUT CHART
            Ce graphique réparti les différents nombres des derniers statuts des courriers
        */
        $courrierStatutsChart = ($chartBuilderInterface->createChart(Chart::TYPE_DOUGHNUT))
            ->setData([
                'labels' => ["en attente", "pris en charge", "avisé", "mis en instance", "NPAI", "non réclamé"],
                'datasets' => [
                    [
                        'data' => [
                            count($statutCourrierRepository->findCourriersByLastStatut(1)),
                            count($statutCourrierRepository->findCourriersByLastStatut(2)),
                            count($statutCourrierRepository->findCourriersByLastStatut(3)),
                            count($statutCourrierRepository->findCourriersByLastStatut(4)),
                            count($statutCourrierRepository->findCourriersByLastStatut(6)),
                            count($statutCourrierRepository->findCourriersByLastStatut(7))
                        ],
                        'backgroundColor' => [
                            'rgb(255, 204, 64)',
                            'rgb(43, 222, 211)',
                            'rgb(16, 36, 200)',
                            'rgb(99, 67, 175)',
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
            BAR CHART TOP EXPEDITEUR
            Ce graphique affiche le top 10 des expéditeurs (clients) ayant envoyé le plus de courriers avec le nombre de courrier associés
        */

        $labels = array();
        $data = array();
        foreach ($statutCourrierRepository->findTopExpediteurs() as $expediteur) {
            array_push($labels, $expediteur["nom"]);
            array_push($data, $expediteur["nombre_courriers"]);
        }


        $topExpediteurs = $chartBuilderInterface->createChart(Chart::TYPE_BAR)
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
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? null,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'nbExpediteurs' => count($expediteurRepository->findAll()),
            'nbCourriersImpression' => $nbCourriersImpression,
            'nbCourriersEnvoi' => $nbCourriersEnvoi,
            'nbCourriersRecu' => $nbCourriersRecu,
            'chart1' => $courrierStatutsChart,
            'chart2' => $topExpediteurs,
            'listeFacteurs' => $facteurRepository->findAll()
        ]);
    }

    #[Route(name: 'statistiques_facteur', path: 'statistiques/facteur')]
    public function ShowFacteur(
        Request $request,
        ExpediteurRepository $expediteurRepository,
        StatutCourrierRepository $statutCourrierRepository,
        ChartBuilderInterface $chartBuilder,
        FacteurRepository $facteurRepository
    ): Response {
        $nomFacteur = $request->get('facteur') ?? null;
        $facteur = $facteurRepository->findOneBy(['nom' => $nomFacteur]);
        if ($nomFacteur == null || $facteur == null) {
            return $this->redirectToRoute('app_statistiques', ['errorMessage' => 'Le nom du facteur saisi est incorrect', 'isError' => true]);
        }

        /* 
            LINE CHART 
            Nombre de courriers envoyés par le facteur par mois depuis sa date de création
        */

        $data = array();
        $dateMin = date_modify($facteur->getCreatedAt(), 'now');
        $dateMax = date_modify($facteur->getCreatedAt(), 'now');
        $dateMax->modify('+1 month');

        $labelsMonth = array();
        $monthList = [
            'janvier',
            'février',
            'mars',
            'avril',
            'mai',
            'juin',
            'juillet',
            'août',
            'septembre',
            'octobre',
            'novembre',
            'decembre'
        ];

        for ($month = 0; $month < 12; $month++) {
            $data[$month] = $statutCourrierRepository->countCourriersByFacteur($nomFacteur, $dateMin, $dateMax)[0]["nbCourrier"];
            $labelsMonth[$month] = $monthList[$month];
            $dateMin->modify('+1 month');
            $dateMax->modify('+1 month');
        }

        $chartFacteur = ($chartBuilder->createChart(Chart::TYPE_LINE))
            ->setData(
                [
                    'labels' => $labelsMonth,
                    'datasets' => [
                        [
                            'data' => $data
                        ]
                    ],

                ]
            )
            ->setOptions([
                'plugins' => [
                    'legend' => [
                        'display' => false
                    ]
                ]
            ]);

        /* 
            DOUGHNUT CHART
            Statuts actules des courriers pris en charge par le facteur
        */

        $facteurId = $facteur->getId();
        $courrierStatutsChart = ($chartBuilder->createChart(Chart::TYPE_DOUGHNUT))
            ->setData([
                'labels' => ["en attente", "pris en charge", "avisé", "mis en instance", "NPAI", "non réclamé"],
                'datasets' => [
                    [
                        'data' => [
                            count($statutCourrierRepository->findCourriersByLastStatut(1, $facteurId)),
                            count($statutCourrierRepository->findCourriersByLastStatut(2, $facteurId)),
                            count($statutCourrierRepository->findCourriersByLastStatut(3, $facteurId)),
                            count($statutCourrierRepository->findCourriersByLastStatut(4, $facteurId)),
                            count($statutCourrierRepository->findCourriersByLastStatut(6, $facteurId)),
                            count($statutCourrierRepository->findCourriersByLastStatut(7, $facteurId))
                        ],
                        'backgroundColor' => [
                            'rgb(255, 204, 64)',
                            'rgb(43, 222, 211)',
                            'rgb(16, 36, 200)',
                            'rgb(99, 67, 175)',
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

        return $this->render('statistique/facteur.html.twig', [
            'errorMessage' => null,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'facteurInfo' => 'infos',
            'chart1' => $chartFacteur,
            'chart2' => $courrierStatutsChart,
            'facteur' => $facteur
        ]);
    }
}
