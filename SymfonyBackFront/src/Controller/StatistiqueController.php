<?php

namespace App\Controller;

use DateTime;
use App\Repository\ExpediteurRepository;
use App\Repository\FacteurRepository;
use App\Repository\StatutCourrierRepository;
use App\Services\StatistiqueService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

            $chartByYear = $this->statistiqueService->GenerateLineChart($year1, $year2);

        }

        $courrierStatutsChart = $this->statistiqueService->GenerateDoughnutChart();

        /* 
            BAR CHART TOP EXPEDITEUR
            Ce graphique affiche le top 10 des expéditeurs (clients) ayant envoyé le plus de courriers avec le nombre de courrier associés
        */

        $topExpediteurs = $this->statistiqueService->GenerateTopExpiditeur();
            
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
        FacteurRepository $facteurRepository
    ): Response {
        $nomFacteur = $request->get('facteur') ?? null;
        $facteur = $facteurRepository->findOneBy(['nom' => $nomFacteur]);
        if ($nomFacteur == null || $facteur == null) {
            return $this->redirectToRoute('app_statistiques', ['errorMessage' => 'Le nom du facteur saisi est incorrect', 'isError' => true]);
        }

        $chartFacteur = $this->statistiqueService->GenerateLineChart2($facteur, $nomFacteur);

        $courrierStatutsChart = $this->statistiqueService->StatutFacteur($facteur);
          

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