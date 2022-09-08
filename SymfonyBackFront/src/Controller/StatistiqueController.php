<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/statistique', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class StatistiqueController extends AbstractController
{
    #[Route('/', name: 'statistique')]
    public function index(): Response
    {
        return $this->render('statistique/index.html.twig', []);
    }
}
