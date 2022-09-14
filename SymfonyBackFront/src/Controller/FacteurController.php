<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/expediteur', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class FacteurController extends AbstractController
{
    #[Route('/', name: 'app_facteur')]
    public function index(): Response
    {
        return $this->render('facteur/index.html.twig', [
            'controller_name' => 'FacteurController',
        ]);
    }
}
