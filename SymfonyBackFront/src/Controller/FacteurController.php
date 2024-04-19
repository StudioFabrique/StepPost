<?php

namespace App\Controller;

use App\Entity\Facteur;
use App\Services\FacteurService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'app_')]
#[IsGranted('ROLE_GESTION')]
class FacteurController extends AbstractController
{
    private $facteurService;

    function __construct(FacteurService $facteurService){
        $this->facteurService = $facteurService;
    }
    
    #[Route('/facteurs', name: 'facteur')]
    public function showFacteurs(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->facteurService-> ShowFacteurService($request);
    }

    #[Route('/nouveauFacteur', 'newFacteur')]
    public function showNewFacteur(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        return $this->facteurService->ShowNewFacteurService($request);
    }

    #[Route('/modifierFacteur/{id}', 'editFacteur')]
    public function editFacteur(Request $request, Facteur $facteur): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->facteurService->EditFacteurService($request, $facteur);
    }

    // #[Route('/supprimerFacteur', 'deleteFacteur')]
    // public function deleteFacteur(Request $request, EntityManagerInterface $em): Response
    // {
    //     if (!$this->getUser()) {
    //         return $this->redirectToRoute('app_login');
    //     }
    //     return $this->facteurService->DeleteFacteurService($request, $em);
    // }

    #[Route(path: '/api/newFacteur', name: 'api_newFacteur')]
    public function newFacteur(Request $request): JsonResponse
    {
        return $this->facteurService->NewFacteurService($request);
    }

    #[Route(path: '/api/editPasswordFacteur', name: 'api_editPasswordFacteur')]
    public function editPasswordFacteur(Request $request): JsonResponse
    {
        return $this->facteurService->EditPasswordFacteurService($request);
    }

    #[Route(path:'/toggleFacteur/{id}', name:'togglefacteur')]
    public function togglefacteur(Request $request, Facteur $facteur, EntityManagerInterface $em)
    {
        return $this->facteurService->togglefacteurService($request, $facteur, $em);

    }
}
