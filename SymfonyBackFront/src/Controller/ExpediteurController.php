<?php

namespace App\Controller;

use App\Entity\Expediteur;
use App\Repository\ExpediteurRepository;
use App\Services\DataFinderService;
use App\Services\ExpediteurService;
use App\Services\MailService;
use App\Services\RequestManagerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Cette classe donne la possibilité de créer, modifier, activer et supprimer un expéditeur.
 */

#[Route('/', name: 'app_')]
#[IsGranted('ROLE_GESTION')]
class ExpediteurController extends AbstractController
{
    private $requestManagerService, $dataFinderService, $expediteurService;

    public function __construct(
        RequestManagerService $requestManagerService,
        DataFinderService $dataFinderService,
        ExpediteurService $expediteurService
    ) {
        $this->requestManagerService = $requestManagerService;
        $this->dataFinderService = $dataFinderService;
        $this->expediteurService = $expediteurService;
    }

    #[Route('/expediteurs', name: 'expediteur')]
    public function index(
        Request $request
    ): Response {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $data = $this->dataFinderService->GetExpediteurs($request);

        $dataPagination = $this->dataFinderService->Paginate($data, $request);

        return $this->render('expediteur/index.html.twig', $this->requestManagerService->GenerateRenderRequest("expediteur", $request, $dataPagination, $data));
    }

    #[Route('/ajouter', name: 'addExpediteur')]
    public function new(Request $request, MailService $mailService): Response
    {
        return $this->expediteurService->newExpediteurService($request, $mailService);
        }

    
    #[Route('/edit/{id}', name: 'editExpediteur')]
    public function edit(Request $request, Expediteur $ancienExpediteur, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em): Response
    {
        return $this->expediteurService->editExpediteurService($request, $ancienExpediteur, $expediteurRepository, $em);
    }

    #[Route('/delete', name: 'deleteExpediteur')]
    public function Delete(Request $request, EntityManagerInterface $em, ExpediteurRepository $expediteurRepository): Response
    {
        return $this->expediteurService->deleteExpediteurService($request, $em, $expediteurRepository);
    }

    #[Route('/activer', name: 'activateExpediteur')]
    public function Activate(Request $request, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        return $this->expediteurService->activateExpediteurService($request, $expediteurRepository, $em, $mailer);
    }

    
}
