<?php

namespace App\Controller;

use App\Form\DateType;
use App\Repository\ExpediteurRepository;
use App\Repository\StatutCourrierRepository;
use App\Repository\StatutRepository;
use App\Services\DataFinder;
use App\Services\DateMaker;
use App\Services\ExportCSV;
use App\Services\MessageService;
use App\Services\RequestManager;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function __construct(
        private DataFinder $dataFinder,
        DateMaker $dateMaker,
        ExportCSV $exportCsv,
        MessageService $messageService,
        RequestManager $requestManager
    ) {
        $this->$dataFinder = $dataFinder;
        $this->dateMaker = $dateMaker;
        $this->messageService = $messageService;
        $this->exportCsv = $exportCsv;
        $this->requestManager = $requestManager;
    }

    /*
    Retourne un template twig avec tous les courriers avec une pagination.
    */
    #[Route('/', name: 'accueil')]
    public function index(Request $request): Response
    {

        // vérification que l'admin soit bien connecté sinon redirection vers la page de connexion
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $rechercheCourrier = $request->get('recherche');

        $form = $this->createForm(DateType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute(
                'app_accueil',
                [
                    'order' => $request->get('order') ?? "DESC",
                    'DateMin' => $form->get('DateMin')->getData(),
                    'DateMax' => $form->get('dateMax')->getData()
                ]
            );
        }

        $data = $this->dataFinder->GetCourriers(
            $request->get('order') ?? "DESC",
            $rechercheCourrier,
            $this->dateMaker->convertDateDefault($request->get('dateMin')),
            $this->dateMaker->convertDateDefault($request->get('dateMax'))
        );

        $dataPagination = $this->dataFinder->Paginate(
            $data,
            $request
        );

        return $this->render('accueil/index.html.twig', $this->requestManager->GenerateRenderRequest('accueil', $request, $dataPagination, $data));
    }

    /* 
        Cette méthode permet d'exporter les données passés en requêtes en format csv (microsoft excel)
    */

    #[Route('/export', name: 'export_csv')]
    public function export(Request $request)
    {
        $data = $this->dataFinder->GetCourriers(
            $request->get('order'),
            $request->get('recherche'),
            $this->dateMaker->convertDateDefault($request->get('dateMin')),
            $this->dateMaker->convertDateDefault($request->get('dateMax'))
        );

        try {
            $this->exportCsv->ExportFile($data);
            return $this->exportCsv->GetFile();
        } catch (Exception) {
            return $this->redirectToRoute('app_accueil', $this->messageService->GetErrorMessage("Exportation CSV", 1), Response::HTTP_SEE_OTHER);
        }
    }
}
