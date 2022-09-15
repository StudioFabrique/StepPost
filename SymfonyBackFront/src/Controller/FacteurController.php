<?php

namespace App\Controller;

use App\Repository\FacteurRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class FacteurController extends AbstractController
{
    #[Route('/facteurs', name: 'facteur')]
    public function showFacteurs(FacteurRepository $facteurRepo, PaginatorInterface $paginatorInterface, Request $request): Response
    {

        $facteurs = $paginatorInterface->paginate(
            $facteurRepo->findAll(),
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('facteur/index.html.twig', [
            'facteurs' => $facteurs
        ]);
    }
}
