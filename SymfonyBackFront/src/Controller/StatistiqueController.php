<?php

namespace App\Controller;

use App\Repository\ExpediteurRepository;
use App\Repository\StatutRepository;
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
    public function index(ExpediteurRepository $expediteurRepository, ChartBuilderInterface $chartBuilderInterface, StatutRepository $statutRepository): Response
    {
        $courrierStatutsChart = $chartBuilderInterface->createChart(Chart::TYPE_PIE);
        $courrierStatutsChart->setData([
            'labels' => ["en attente", "pris en charge", "mis en instance", "avisé", "distribué", "NPAI", "retour à l'expéditeur"],
            'datasets' => [
                'data' => [
                    count($statutRepository->find(1)->getStatutsCourrier()),
                    count($statutRepository->find(2)->getStatutsCourrier()),
                    count($statutRepository->find(3)->getStatutsCourrier()),
                    count($statutRepository->find(4)->getStatutsCourrier()),
                    count($statutRepository->find(5)->getStatutsCourrier()),
                    count($statutRepository->find(6)->getStatutsCourrier()),
                    count($statutRepository->find(7)->getStatutsCourrier())
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
