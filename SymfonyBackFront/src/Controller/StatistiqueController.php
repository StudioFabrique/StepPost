<?php

namespace App\Controller;

use App\Repository\ExpediteurRepository;
use App\Repository\FacteurRepository;
use App\Repository\StatutCourrierRepository;
use DateTime;
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
    /* 
        Statistiques globales concernant les courriers et les expéditeurs
    */

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

        $nbCourriersImpression = count($statutCourrierRepository->FindCourrierImpression()); // Le nombre de bordereaux des courriers/colis imprimés
        $nbCourriersEnvoi = count($statutCourrierRepository->FindCourrierEnvoi()); // Le nombre de courriers/colis envoyés
        $nbCourriersRecu = count($statutCourrierRepository->FindCourrierRecu()); // Le nombre de courrier/colis distribués

        /* 
            BAR CHART
            Ce graphique réparti les différents nombres des derniers statuts des courriers par mois.
            Si deux dates sont sélectionnées, alors les deux périodes sont affichés dans le même graphique.
        */

        $date1 = $request->get('date1') != null ? new DateTime($request->get('date1')) : null;
        $date2 = $request->get('date2') != null ? new DateTime($request->get('date2')) : null;
        $year1 = $request->get('annee1') != null ? new DateTime($request->get('annee1')) : null;
        $year2 = $request->get('annee2') != null ? new DateTime($request->get('annee2')) : null;
        var_dump($request->get('annee1'));
        var_dump($year1);

        $dateArray = array();
        if ($date1 != null) {
            array_push($dateArray, $date1);
        }
        if ($date2 != null) {
            array_push($dateArray, $date2);
        }
        if ($year1 != null) {
            array_push($dateArray, $year1);
        }
        if ($year2 != null) {
            array_push($dateArray, $year2);
        }

        if ($date1 != null || $date2 != null || $year1 != null || $year2 != null) {
            $i = 0;
            $dateLabels = array();
            $dataBordereaux = array();
            $dataEnvoi = array();
            $dataRecu = array();

            foreach ($dateArray as $date) {
                $dateLabels[$i] = $year1 != null || $year2 != null ? date_format($date, 'Y') : date_format($date, 'M Y');
                $dataBordereaux[$i] = count($statutCourrierRepository->findCourrierImpression($date, $year1 != null || $year2 != null ? true : false));
                $dataEnvoi[$i] = count($statutCourrierRepository->findCourrierEnvoi($date, $year1 != null || $year2 != null ? true : false));
                $dataRecu[$i] = count($statutCourrierRepository->findCourrierRecu($date, $year1 != null || $year2 != null ? true : false));
                $i++;
            }

            $chartByDate = $chartBuilderInterface->createChart(Chart::TYPE_BAR)
                ->setData(
                    [
                        'labels' => $dateLabels,
                        'datasets' => [
                            [
                                'label' => 'bordereaux générés',
                                'data' => $dataBordereaux,
                                'backgroundColor' => [
                                    'rgb(255, 204, 64)'
                                ]
                            ],
                            [
                                'label' => 'courriers/colis pris en charges',
                                'data' => $dataEnvoi,
                                'backgroundColor' => [
                                    'rgb(43, 222, 211)'
                                ]
                            ],
                            [
                                'label' => 'courriers/colis distribués',
                                'data' => $dataRecu,
                                'backgroundColor' => [
                                    'rgb(16, 36, 200)'
                                ]
                            ]
                        ]
                    ]
                )
                ->setOptions([
                    'plugins' => [
                        'legend' => [
                            'display' => false
                        ]
                    ]
                ]);
        }

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
            'chartByDate' => $chartByDate ?? null,
            'listeFacteurs' => $facteurRepository->findAll()
        ]);
    }

    /* 
        Statistiques pour les facteurs
    */
    #[Route(name: 'statistiques_facteur', path: '/facteur')]
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
        $dateMin = date_create(date_format($facteur->getCreatedAt(), "Y-m-d"));
        $dateMax = date_create(date_format($facteur->getCreatedAt(), "Y-m-d"))->modify('+1 month');

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


        $dateDiff = date_diff(new DateTime('now'), $facteur->getCreatedAt());
        $monthDiff = intval($dateDiff->format('%m')) + 12 * intval($dateDiff->format('%y'));
        for ($month = 0; $month < $monthDiff + 1; $month++) {
            $data[$month] = $statutCourrierRepository->countCourriersByFacteur($nomFacteur, $dateMin, $dateMax)[0]["nbCourrier"];
            $labelsMonth[$month] = date_format($dateMin, 'Y') . '-' . $monthList[date_format($dateMin, 'm') - 1];
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
