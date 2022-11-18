<?php

namespace App\Controller;

use App\Form\DateType;
use App\Repository\ExpediteurRepository;
use App\Repository\StatutCourrierRepository;
use App\Repository\StatutRepository;
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
    Cette méthode affiche tous les courriers avec une pagination.
    */
    #[Route('/', name: 'accueil')]
    public function index(
        StatutCourrierRepository $statutCourrierRepo, // Le répertoire contenant un tableau de tous les courriers
        Request $request,
        PaginatorInterface $paginator, // Interface de pagination
        StatutRepository $statuts,
        ExpediteurRepository $expediteurRepository,
    ): Response {

        // vérification que l'admin soit bien connecté sinon redirection vers la page de connexion
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $order = $request->get('order') ?? "DESC";
        $rechercheCourrier = $request->get('recherche') ?? null;
        $currentPage = $request->get('currentPage') ?? 1;

        $dateMin = $request->get('dateMin') != null ? date_create($request->get('dateMin')) : null;
        $dateMax = $request->get('dateMax') != null ? date_create($request->get('dateMax')) : null;

        if ($rechercheCourrier == null) {
            $data = $statutCourrierRepo->findCourriers($order, $dateMin ?? null, $dateMax ?? null);
        } else {
            is_numeric($rechercheCourrier) ? $data = $statutCourrierRepo->findCourriersByBordereau($rechercheCourrier)
                : (is_string($rechercheCourrier) ? $data = $statutCourrierRepo->findCourriersByNomPrenom($rechercheCourrier)
                    : $data = $statutCourrierRepo->findCourriers($order));
        }

        $form = $this->createForm(DateType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('app_accueil', ['order' => $order, 'DateMin' => $form->get('DateMin')->getData(), 'DateMax' => $form->get('dateMax')->getData()]);
        }

        $courriers = $paginator->paginate(
            $data,
            $request->query->getInt('page') < 2 ? $currentPage : $request->query->getInt('page')
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
            'dateMax' => $request->get('dateMax') ?? null
        ]);
    }

    /* 
        Cette méthode permet d'exporter les données passés en requêtes en format csv (microsoft excel)
    */

    #[Route('/exportCsv', name: 'export_csv')]
    public function exportCsv(Request $request, StatutCourrierRepository $statutCourrierRepository)
    {
        $dateMin = $request->get('dateMin') != null ? date_create($request->get('dateMin')) : null;
        $dateMax = $request->get('dateMax') != null ? date_create($request->get('dateMax')) : null;

        $data = $statutCourrierRepository->findCourriers($request->get('order'), $dateMin ?? null, $dateMax ?? null);
        $path = $_ENV['CSV_EXPORT_PATH'] . '/courriers-' . date_format(new DateTime('now'), 'h-i') . '.csv';
        $csvCourriers[0] = ['Date', 'Expéditeur', 'Statut', 'Bordereau', 'Type', 'Nom', 'Prénom', 'Adresse', 'Code Postal', 'Ville'];
        $i = 1;
        foreach ($data as $courrier) {
            $csvCourriers[$i] = [
                $courrier['date'],
                $courrier['nomExpediteur'],
                $courrier['etat'],
                $courrier['bordereau'],
                $courrier['type'] == 0 ? 'Lettre avec suivi' : ($courrier['type'] == 1 ? 'Lettre avec accusé de reception' : 'Colis'),
                $courrier['nom'],
                $courrier['prenom'],
                $courrier['adresse'],
                $courrier['codePostal'],
                $courrier['ville']
            ];
            $i++;
        }

        try {
            $writer = Writer::createFromPath($path, 'w');
            $writer->insertAll($csvCourriers);
            return $this->redirectToRoute('app_accueil', ['errorMessage' => 'Le fichier a bien été exporté au répertoire ' . $path]);
        } catch (CannotInsertRecord $e) {
            $e->getRecord();
            return $this->redirectToRoute('app_admin_add', ['errorMessage' => "L'exportation en .csv a échoué", 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }
}
