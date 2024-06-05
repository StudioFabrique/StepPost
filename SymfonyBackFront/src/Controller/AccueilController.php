<?php

namespace App\Controller;

use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\DataFinderService;
use App\Services\ExportCSVService;
use App\Services\ExportXLSService;
use App\Services\MessageService;
use App\Services\RequestManagerService;

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
    private $dataFinderService, $exportCsvService, $messageService, $requestManagerService, $exportXlsService;
    /**
     * Constructeur
     */
    public function __construct(
        DataFinderService $dataFinderService,
        ExportCSVService $exportCsvService,
        ExportXLSService $exportXlsService,
        MessageService $messageService,
        RequestManagerService $requestManagerService
    ) {
        $this->dataFinderService = $dataFinderService;
        $this->messageService = $messageService;
        $this->exportCsvService = $exportCsvService;
        $this->requestManagerService = $requestManagerService;
        $this->exportXlsService = $exportXlsService;
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

        $data = $this->dataFinderService->GetCourriers($request, $this->getUser());
        $dataPagination = $this->dataFinderService->Paginate(
            $data,
            $request
        );

        return $this->render('accueil/index.html.twig', $this->requestManagerService->GenerateRenderRequest('accueil', $request, $dataPagination, $data));
    }

    /**
     * Récupère et affiche tous les détails d'un expéditeur
     * @return \Symfony\Component\HttpFoundation\Response
     */

    #[Route('/detailsExpediteur', name: 'detailsExpediteur')]
    public function DetailsExpediteur(Request $request): Response
    {
        return $this->render('expediteur/details.html.twig', $this->requestManagerService->GenerateRenderRequest('detailsExpediteur', $request));
    }

    /**
     * Exporte les données passés en requêtes en format csv (microsoft excel)
     * @return \Symfony\Component\HttpFoundation\Response
     */

    #[Route('/export', name: 'export')]
    public function export(Request $request)
    {
        $exportType = $request->get('type');
        $data = $this->dataFinderService->GetCourriers($request, $this->getUser());

        try {
            if ($exportType === 'Csv' || $exportType === 'Xls') {
                $this->{'export' . $exportType . 'Service'}->ExportFile($data); // like : call_user_func(array($this, 'export' . $exportType))->exportFile($data);
                return $this->{'export' . $exportType . 'Service'}->GetFile();
            }
            throw new Exception("Unknow exportation problem", 1);
        } catch (Exception $e) {
            dd($e);
            return $this->redirectToRoute('app_accueil', $this->messageService->GetErrorMessage("Exportation CSV", 1), Response::HTTP_SEE_OTHER);
        }
    }
}
