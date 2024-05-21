<?php

namespace App\Services;

use App\Entity\Facteur;
use App\Repository\ExpediteurRepository;
use DateTime;
use App\Repository\StatutCourrierRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StatistiqueService extends AbstractController {
    private $statutCourrierRepository, $chartBuilderInterface, $chartBuilder;

    public function __construct(StatutCourrierRepository $statutCourrierRepository, ChartBuilderInterface $chartBuilderInterface, ChartBuilderInterface $chartBuilder,) {
        $this->statutCourrierRepository = $statutCourrierRepository;
        $this->chartBuilderInterface = $chartBuilderInterface;
        $this->chartBuilder = $chartBuilder;
        
    }

    function GenerateBarChart(DateTime $date1, DateTime $date2): Chart  {
        $dateArray = array();
            if ($date1 != null) {
                array_push($dateArray, $date1);
            }
            if ($date2 != null) {
                array_push($dateArray, $date2);
            }

            $i = 0;
            $dateLabels = array();
            $dataBordereaux = array();
            $dataEnvoi = array();
            $dataRecu = array();

            foreach ($dateArray as $date) {
                $dateLabels[$i] = date_format($date, 'M Y');
                $dataBordereaux[$i] = count($this->statutCourrierRepository->findCourrierImpression($date));
                $dataEnvoi[$i] = count($this->statutCourrierRepository->findCourrierEnvoi($date));
                $dataRecu[$i] = count($this->statutCourrierRepository->findCourrierRecu($date));
                $i++;
            }

        return $this->chartBuilderInterface->createChart(Chart::TYPE_BAR)
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
                                'rgb(55,126,184)'
                            ]
                        ],
                        [
                            'label' => 'courriers/colis distribués',
                            'data' => $dataRecu,
                            'backgroundColor' => [
                                'rgb(77,175,74)'
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


    function GenerateLineChart(DateTime $year1, DateTime $year2): Chart {
        $data1 = array();
        $data2 = array();
        $i = 0;
        for ($month = 1; $month < 13; $month++) {
            $datetime1 = $year1 != null ? new DateTime($year1->format('Y') . (count_chars($month) < 2 ? '-0' . $month : '-' . $month) . '-01') : null;
            $datetime2 = $year2 != null ? new DateTime($year2->format('Y') . (count_chars($month) < 2 ? '-0' . $month : '-' . $month) . '-01') : null;
            $data1[$i] = count($this->statutCourrierRepository->findCourrierRecu($datetime1));
            $data2[$i] = count($this->statutCourrierRepository->findCourrierRecu($datetime2));
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

          /* 
            LINE CHART
            Si deux dates sont sélectionnées, alors les deux périodes sont affichés dans le même graphique.
            */

        return $this->chartBuilderInterface->createChart(Chart::TYPE_LINE)
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
            LINE CHART 
            Nombre de courriers envoyés par le facteur par mois depuis sa date de création
        */

    function GenerateLineChart2($facteur, $nomFacteur): Chart{
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
            $data1[$month] = $this->statutCourrierRepository->countCourriersByFacteurAndStatut($nomFacteur, 2, $dateMin, $dateMax)[0]["nbCourrier"];
            $data2[$month] = $this->statutCourrierRepository->countCourriersByFacteurAndStatut($nomFacteur, 5, $dateMin, $dateMax)[0]["nbCourrier"];
            $labelsMonth[$month] = date_format($dateMin, 'Y') . '-' . $monthList[date_format($dateMin, 'm') - 1];
            $dateMin->modify('+1 month');
            $dateMax->modify('+1 month');
        }
        

        return $this->chartBuilder->createChart(Chart::TYPE_BAR)
            ->setData(
                [
                    'labels' => $labelsMonth,
                    'datasets' => [
                        [
                            'label' => 'pris en charge',
                            'data' => $data1,
                            'borderColor' => [
                                'rgb(55,126,184)'
                            ]
                        ],
                        [
                            'label' => 'distribués',
                            'data' => $data2,
                            'borderColor' => [
                                'rgb(77,175,74)'
                            ]
                            
                        ]
                    ]
                ]
            )
            ->setOptions([
                'scales' => [
                    'x' => [
                        'categoryPercentage' => 1.0,
                        'barPercentage' => 2.0
                    ]
                ],
                'plugins' => [
                    'legend' => [
                        'display' => false
                    ]
                ],
                
            ]);

    }

    function ReturnDatas($facteur, $nomFacteur): Chart{
        $data3 = array();
        $data4 = array();
        $dateMin2 = date_create($facteur->getCreatedAt()->format("Y-m-d"));
        $dateMax2 = clone $dateMin2; // Crée une copie de $dateMin2
        $dateMax2->modify('+1 year');
        $dateDiff = date_diff(new DateTime('now'), $facteur->getCreatedAt());
        $yearDiff = intval($dateDiff->format('%y'));
        $labelYear=array();
        $anneeDebut = $dateMin2->format('Y');
        for($year = 0; $year<$yearDiff +1; $year++){
            $data3[$anneeDebut + $year] = $this->statutCourrierRepository->countCourriersByFacteurAndStatut($nomFacteur, 2, $dateMin2, $dateMax2)[0]["nbCourrier"];
            $data4[$anneeDebut + $year] = $this->statutCourrierRepository->countCourriersByFacteurAndStatut($nomFacteur, 5, $dateMin2, $dateMax2)[0]["nbCourrier"];
            $labelYear[$year] = date_format($dateMin2, 'Y');
            $dateMin2->modify('+1 year');
            $dateMax2->modify('+1 year');
        }

        return $this->chartBuilder->createChart(Chart::TYPE_BAR)
            ->setData(
                [
                    'labels' => $labelYear,
                    'datasets' => [
                        [
                            'label' => 'pris en charge',
                            'data' => $data3,
                            'borderColor' => [
                                'rgb(55,126,184)'
                            ]
                        ],
                        [
                            'label' => 'distribués',
                            'data' => $data4,
                            'borderColor' => [
                                'rgb(77,175,74)'
                            ]
                            
                        ]
                    ]
                ]
            )
            ->setOptions([
                'scales' => [
                    'x' => [
                        'categoryPercentage' => 1.0,
                        'barPercentage' => 2.0
                    ]
                ],
                'plugins' => [
                    'legend' => [
                        'display' => false
                    ]
                ],
                
            ]);
        

        

    }


    /* 
            DOUGHNUT CHART
            Statuts actules des courriers pris en charge par le facteur
        */
        
    function StatutFacteur($facteur){
        $facteurId = $facteur->getId();
        return $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT)
            ->setData([
                'labels' => ["en attente", "pris en charge", "avisé", "mis en instance", "distribué", "NPAI", "non réclamé", "erreur de libellé"],
                'datasets' => [
                    [
                        'data' => [
                            count($this->statutCourrierRepository->findCourriersByLastStatut(1, $facteurId)),
                            count($this->statutCourrierRepository->findCourriersByLastStatut(2, $facteurId)),
                            count($this->statutCourrierRepository->findCourriersByLastStatut(3, $facteurId)),
                            count($this->statutCourrierRepository->findCourriersByLastStatut(4, $facteurId)),
                            count($this->statutCourrierRepository->findCourriersByLastStatut(5, $facteurId)),
                            count($this->statutCourrierRepository->findCourriersByLastStatut(6, $facteurId)),
                            count($this->statutCourrierRepository->findCourriersByLastStatut(7, $facteurId)),
                            count($this->statutCourrierRepository->findCourriersByLastStatut(8, $facteurId))
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
    }

        /* 
            DOUGHNUT CHART
            Ce graphique réparti les différents nombres des derniers statuts des courriers
        */

    function GenerateDoughnutChart(){
        return ($this->chartBuilderInterface->createChart(Chart::TYPE_DOUGHNUT))
        ->setData([
            'labels' => ["en attente", "pris en charge", "avisé", "mis en instance", "distribué", "NPAI", "non réclamé"],
            'datasets' => [
                [
                    'data' => [
                        count($this->statutCourrierRepository->findCourriersByLastStatut(1)),
                        count($this->statutCourrierRepository->findCourriersByLastStatut(2)),
                        count($this->statutCourrierRepository->findCourriersByLastStatut(3)),
                        count($this->statutCourrierRepository->findCourriersByLastStatut(4)),
                        count($this->statutCourrierRepository->findCourriersByLastStatut(5)),
                        count($this->statutCourrierRepository->findCourriersByLastStatut(6)),
                        count($this->statutCourrierRepository->findCourriersByLastStatut(7))
                    ],
                    'backgroundColor' => [
                        'rgb(228,26,28)',
                        'rgb(55,126,184)',
                        'rgb(255,127,0)',
                        'rgb(152,78,163)',
                        'rgb(77,175,74)',
                        'rgb(20, 10, 130)',
                        'rgb(197, 48, 48)'
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

    }
    
    function GenerateTopExpiditeur(){
        $labels = array();
        $data = array();
        foreach ($this->statutCourrierRepository->findTopExpediteurs() as $expediteur) {
            array_push($labels, $expediteur["nom"]);
            array_push($data, $expediteur["nombre_courriers"]);
        }

        return $this->chartBuilderInterface->createChart(Chart::TYPE_BAR)
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
            
    }
}
