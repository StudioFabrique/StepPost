<?php

namespace App\Controller;

use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\DataFinder;
use App\Services\ExportCSV;
use App\Services\MessageService;
use App\Services\RequestManager;

/**
 * Cette classe est le point d'entrée de l'application après que 
 * l'utilisateur (Administrateur) se soit connecté à l'application.
 * Par l'intermédiaire de cette classe, l'administrateur va pouvoir gérer les différents
 * courriers présents dans la base données.
 */

#[Route('/accueil', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class AccueilController extends AbstractController
{
    private $dataFinder, $exportCsv, $messageService, $requestManager;
    /**
     * Constructeur
     */
    public function __construct(
        DataFinder $dataFinder,
        ExportCSV $exportCsv,
        MessageService $messageService,
        RequestManager $requestManager
    ) {
        $this->dataFinder = $dataFinder;
        $this->messageService = $messageService;
        $this->exportCsv = $exportCsv;
        $this->requestManager = $requestManager;
    }

    /**
     * Retourne un template twig avec tous les courriers avec une pagination.
     * @param Request $request
     */

    #[Route('/', name: 'accueil')]
    public function index(Request $request): Response
    {
        // vérification que l'admin soit bien connecté sinon redirection vers la page de connexion
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $data = $this->dataFinder->GetCourriers($request, $this->getUser());
        $dataPagination = $this->dataFinder->Paginate(
            $data,
            $request
        );

        return $this->render('accueil/index.html.twig', $this->requestManager->GenerateRenderRequest('accueil', $request, $dataPagination, $data));
    }

    /**
     * Récupère et affiche tous les détails d'un expéditeur
     * @return \Symfony\Component\HttpFoundation\Response
     */

    #[Route('/detailsExpediteur', name: 'detailsExpediteur')]
    public function DetailsExpediteur(Request $request): Response
    {
        return $this->render('expediteur/details.html.twig', $this->requestManager->GenerateRenderRequest('detailsExpediteur', $request));
    }

    /**
     * Exporte les données passés en requêtes en format csv (microsoft excel)
     * @return \Symfony\Component\HttpFoundation\Response
     */

    #[Route('/export', name: 'export_csv')]
    public function export(Request $request)
    {
        $data = $this->dataFinder->GetCourriers($request, $this->getUser());

        try {
            $this->exportCsv->ExportFile($data);
            return $this->exportCsv->GetFile();
        } catch (Exception) {
            return $this->redirectToRoute('app_accueil', $this->messageService->GetErrorMessage("Exportation CSV", 1), Response::HTTP_SEE_OTHER);
        }
    }
}
