<?php

namespace App\Controller;

use App\Form\DateType;
use App\Repository\ExpediteurRepository;
use App\Repository\StatutCourrierRepository;
use App\Repository\StatutRepository;
use App\Services\DataFinder;
use App\Services\DateMaker;
use App\Services\ExportCSV;
use DateTime;
use DateTimeZone;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use League\Csv\Writer;
use League\Csv\CannotInsertRecord;

/*
Cette classe est le point d'entrée de l'application après que 
l'utilisateur (Administrateur) se soit connecté à l'application.
Par l'intermédiaire de cette classe, l'administrateur va pouvoir gérer les différents
courriers présents dans la base données.
*/

#[Route('/accueil', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class AccueilController extends AbstractController
{
    /*
    Retourne un template twig avec tous les courriers avec une pagination.
    */
    #[Route('/', name: 'accueil')]
    public function index(
        StatutCourrierRepository $statutCourrierRepo, // Le répertoire contenant un tableau de tous les courriers
        Request $request,
        PaginatorInterface $paginator, // Interface de pagination
        StatutRepository $statuts,
        ExpediteurRepository $expediteurRepository,
        DateMaker $dateMaker,
        DataFinder $dataFinder
    ): Response {

        // vérification que l'admin soit bien connecté sinon redirection vers la page de connexion
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $order = $request->get('order') ?? "DESC";
        $currentPage = $request->get('currentPage') ?? 1;
        $rechercheCourrier = $request->get('recherche');

        $form = $this->createForm(DateType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute(
                'app_accueil',
                [
                    'order' => $order,
                    'DateMin' => $form->get('DateMin')->getData(),
                    'DateMax' => $form->get('dateMax')->getData()
                ]
            );
        }

        $data = $dataFinder->GetCourriers(
            $statutCourrierRepo,
            $order,
            $rechercheCourrier,
            $dateMaker->convertDateDefault($request->get('dateMin')),
            $dateMaker->convertDateDefault($request->get('dateMax'))
        );

        $courriers = $dataFinder->PaginateAndClean(
            $data,
            $paginator,
            $request->query->getInt('page'),
            $currentPage
        );

        return $this->render('accueil/index.html.twig', [

            'isError' => $request->get('isError') ?? false,
            'courriers' => $courriers,
            'statuts' => $statuts->findAll(),
            'order' => $order == "DESC" ? "ASC" : "DESC",
            'isSearching' => is_integer($rechercheCourrier) ? true : (is_string($rechercheCourrier) ? true : false),
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'nbCourriersTotal' => count($data),
            'currentPage' => $request->query->getInt('page') > 1 ? $request->query->getInt('page') <= 2 : $currentPage,
            'errorMessage' => $request->get('errorMessage') ?? null,
            'dateMin' => $request->get('dateMin') ?? null,
            'dateMax' => $request->get('dateMax') ?? null,
            'recherche' => $request->get('recherche')
        ]);
    }

    /* 
        Cette méthode permet d'exporter les données passés en requêtes en format csv (microsoft excel)
    */

    #[Route('/exportCsv', name: 'export_csv')]
    public function exportCsv(Request $request, StatutCourrierRepository $statutCourrierRepository, ExportCSV $export)
    {
        $dateMin = $request->get('dateMin') != null ? date_create($request->get('dateMin')) : null;
        $dateMax = $request->get('dateMax') != null ? date_create($request->get('dateMax')) : null;

        $data = $statutCourrierRepository->findCourriers($request->get('order'), $dateMin ?? null, $dateMax ?? null);
        $exportCsv = $export->ExportFileToPath($data);
        if ($exportCsv) {
            return $this->redirectToRoute('app_accueil', ['errorMessage' => 'Le fichier a bien été exporté au répertoire ' . $export->GetExportPath()]);
        } else {
            return $this->redirectToRoute('app_admin_add', ['errorMessage' => "L'exportation en .csv a échoué", 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }
}
