<?php

namespace App\Controller;

use DateTime;
use App\Repository\ExpediteurRepository;
use App\Repository\FacteurRepository;
use App\Repository\StatutCourrierRepository;
use App\Services\StatistiqueService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/statistiques', name: 'app_')]
#[IsGranted('ROLE_GESTION')]
class StatistiqueController extends AbstractController
{
    private $statistiqueService;

    public function __construct(StatistiqueService $statistiqueService) {
        $this->statistiqueService = $statistiqueService;
    }

    /* 
        Retourne un template twig avec des statistiques globales concernant les courriers et les expéditeurs
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
        */

        $date1 = $request->get('date1') != null ? new DateTime($request->get('date1')) : null;
        $date2 = $request->get('date2') != null ? new DateTime($request->get('date2')) : null;
        $year1 = $request->get('annee1') != null ? new DateTime($request->get('annee1') . "-01-01") : null;
        $year2 = $request->get('annee2') != null ? new DateTime($request->get('annee2') . "-01-01") : null;

        if (($date1 != null || $date2 != null) && ($year1 == null && $year2 == null)) {

           $chartByDate = $this->statistiqueService->GenerateBarChart($date1, $date2, $year1, $year2);
            
        } else if (($date1 == null || $date2 == null) && ($year1 != null || $year2 != null)) {

            /* 
            LINE CHART
            Si deux dates sont sélectionnées, alors les deux périodes sont affichés dans le même graphique.
            */

            $data1 = array();
            $data2 = array();
            $i = 0;
            for ($month = 1; $month < 13; $month++) {
                $datetime1 = $year1 != null ? new DateTime($year1->format('Y') . (count_chars($month) < 2 ? '-0' . $month : '-' . $month) . '-01') : null;
                $datetime2 = $year2 != null ? new DateTime($year2->format('Y') . (count_chars($month) < 2 ? '-0' . $month : '-' . $month) . '-01') : null;
                $data1[$i] = count($statutCourrierRepository->findCourrierRecu($datetime1));
                $data2[$i] = count($statutCourrierRepository->findCourrierRecu($datetime2));
                $i++;
            }
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

            $chartByYear = ($chartBuilderInterface->createChart(Chart::TYPE_LINE))
                ->setData(
                    [
                        'labels' => $monthList,
                        'datasets' => [
                            [
                                'label' => $year1 != null ? "nombre de courriers/colis distribués pour l'année " . $year1->format('Y') : null,
                                'data' => $year1 != null ? $data1 : null,
                                'borderColor' => [
                                    'rgb(255, 204, 64)'
                                ]
                            ],
                            [
                                'label' => $year2 != null ? "nombre de courriers/colis distribués pour l'année " . $year2->format('Y') : null,
                                'data' => $year2 != null ? $data2 : null,
                                'borderColor' => [
                                    'rgb(43, 222, 211)'
                                ]
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
        }





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
                            'rgb(228,26,28)',
                            'rgb(55,126,184)',
                            'rgb(77,175,74)',
                            'rgb(152,78,163)',
                            'rgb(255,127,0)',
                            'rgb(255,255,51)',
                            'rgb(166,86,40)'
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
            'chartByYear' => $chartByYear ?? null,
            'listeFacteurs' => $facteurRepository->findAll(),
            'year1' => $year1 != null ? $year1->format('Y') : null,
            'year2' => $year2 != null ? $year2->format('Y') : null
        ]);
    }

    /* 
        Retourne un template twig avec des statistiques concernant un facteur
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

        $data1 = array(); // pris en charge
        $data2 = array(); // distribué
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
            $data1[$month] = $statutCourrierRepository->countCourriersByFacteurAndStatut($nomFacteur, 2, $dateMin, $dateMax)[0]["nbCourrier"];
            $data2[$month] = $statutCourrierRepository->countCourriersByFacteurAndStatut($nomFacteur, 5, $dateMin, $dateMax)[0]["nbCourrier"];
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
                            'label' => 'pris en charge',
                            'data' => $data1,
                            'borderColor' => [
                                'rgb(43, 222, 211)'
                            ]
                        ],
                        [
                            'label' => 'distribués',
                            'data' => $data2,
                            'borderColor' => [
                                'rgb(255, 204, 64)'
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

        /* 
            DOUGHNUT CHART
            Statuts actules des courriers pris en charge par le facteur
        */

        $facteurId = $facteur->getId();
        $courrierStatutsChart = ($chartBuilder->createChart(Chart::TYPE_DOUGHNUT))
            ->setData([
                'labels' => ["en attente", "pris en charge", "avisé", "mis en instance", "distribué", "NPAI", "non réclamé", "erreur de libellé"],
                'datasets' => [
                    [
                        'data' => [
                            count($statutCourrierRepository->findCourriersByLastStatut(1, $facteurId)),
                            count($statutCourrierRepository->findCourriersByLastStatut(2, $facteurId)),
                            count($statutCourrierRepository->findCourriersByLastStatut(3, $facteurId)),
                            count($statutCourrierRepository->findCourriersByLastStatut(4, $facteurId)),
                            count($statutCourrierRepository->findCourriersByLastStatut(5, $facteurId)),
                            count($statutCourrierRepository->findCourriersByLastStatut(6, $facteurId)),
                            count($statutCourrierRepository->findCourriersByLastStatut(7, $facteurId)),
                            count($statutCourrierRepository->findCourriersByLastStatut(8, $facteurId))
                        ],
                        'backgroundColor' => [
                            'rgb(50, 204, 64)',
                            'rgb(43, 222, 211)',
                            'rgb(16, 36, 200)',
                            'rgb(99, 67, 175)',
                            'rgb(238, 155, 49)',
                            'rgb(193, 52, 21)',
                            'rgb(192, 196, 198)',
                            'rgb(100, 120, 52)'
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
