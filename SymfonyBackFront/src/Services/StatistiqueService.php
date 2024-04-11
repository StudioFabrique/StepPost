<?php

namespace App\Services;

use DateTime;
use App\Repository\StatutCourrierRepository;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatistiqueService {
    private $statutCourrierRepository, $chartBuilderInterface;

    public function __construct(StatutCourrierRepository $statutCourrierRepository, ChartBuilderInterface $chartBuilderInterface) {
        $this->statutCourrierRepository = $statutCourrierRepository;
        $this->chartBuilderInterface = $chartBuilderInterface;
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

    function GenerateLineChart() {}
}