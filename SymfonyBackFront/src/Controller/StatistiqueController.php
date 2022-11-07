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
        // Le nombre d'expéditeurs inscrits et actifs ces dernières x heures
        $nbCourriersImpression = count($statutCourrierRepository->FindCourrierImpression()); // Le nombre de bordereaux des courriers/colis imprimé ces dernières x heures
        $nbCourriersEnvoi = count($statutCourrierRepository->FindCourrierEnvoi()); // Le nombre de courriers/colis envoyés ces dernières x heures
        $nbCourriersRecu =  count($statutCourrierRepository->FindCourrierRecu());

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
            'errorMessage' => null,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'nbExpediteurs' => count($expediteurRepository->findAll()),
            'nbCourriersImpression' => $nbCourriersImpression,
            'nbCourriersEnvoi' => $nbCourriersEnvoi,
            'nbCourriersRecu' => $nbCourriersRecu,
            'chart1' => $topExpediteurs
        ]);
    }
}
