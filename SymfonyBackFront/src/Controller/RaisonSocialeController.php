<?php

namespace App\Controller;

use Exception;
use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use App\Repository\ExpediteurRepository;
use App\Services\RaisonSocialeService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/RaisonSociale', name: 'app_')]
#[IsGranted('ROLE_GESTION')]
class RaisonSocialeController extends AbstractController
{

    private $raisonSocialeService;

    function __construct(RaisonSocialeService $raisonSocialeService){
        $this->raisonSocialeService=$raisonSocialeService;
    }
    
    #[Route('/', name: 'raisonSociale')]
    public function ShowRaisonsSociales(PaginatorInterface $paginator, Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->raisonSocialeService->SocialReasonShow($request, $paginator);
    }

    #[Route('/RaisonSocialeClients', name: 'clientsRaisonSociale')]
    public function ShowClientsRaisonsSociales(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        return $this->raisonSocialeService->SocialReasonShowClient($request);
    }

    #[Route('/ajouterRaisonSociale', name: 'addRaisonSociale')]
    public function AddRaisonSociale(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return  $this->raisonSocialeService->SocialReasonAdd($request);
    }

    #[Route('/modifierRaisonSociale', name: 'editRaisonSociale')]
    public function EditRaisonSociale(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->raisonSocialeService->SocialReasonEdit($request);
    }

    #[Route('/supprimerRaisonSociale', name: 'deleteRaisonSociale')]
    public function RemoveRaisonSociale(Request $request): RedirectResponse
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->raisonSocialeService->SocialReasonRemove($request);
    }

    #[Route('/ajouterClient', name: 'addClientRaisonSociale')]
    public function AddClientFrom(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->raisonSocialeService->SocialReasonClientFrom($request);
    }

    #[Route('/ajouterLeClient', name: 'addTheClientRaisonSociale')]
    public function AddTheClientFrom(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

       return $this->raisonSocialeService->SocialReasonTheCLientFrom($request, $em);
    }

    #[Route('/detacherClient', name: 'deleteClientRaisonSociale')]
    public function DeleteClientFrom(Request $request, ClientRepository $clientRepository, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em): RedirectResponse
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->raisonSocialeService->SocialReasonDelteCLient($request, $em);
    }
}