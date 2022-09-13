<?php

namespace App\Controller;

use App\Entity\Courrier;
use App\Repository\StatutCourrierRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/accueil', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class AccueilController extends AbstractController
{
    #[Route('/', name: 'accueil')]
    public function index(
        StatutCourrierRepository $statutCourrierRepo,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $order = $request->get('order') ?? "DESC";

        $donner = $statutCourrierRepo->findStatusOneAll($order);
        $courriers = $paginator->paginate(
            $donner,
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('accueil/index.html.twig', [
            'courriers' => $courriers,
            'order' => $order == "DESC" ? "ASC" : "DESC"
        ]);
    }

    // Fonction de redirection lorsque l'input du formulaire de la page accueil (gestion des courriers)
    // est envoyé.

    #[Route('/suivi/{id}', name: 'suiviId')]
    public function indexbyid(
        Courrier $id,
        StatutCourrierRepository $statutsCourrierRepo
    ): Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $statutsCourrier = $statutsCourrierRepo->findBy(["courrier" => $id], ["courrier" => "DESC"]);

        return $this->render('suivi_detail/index.html.twig', [
            'courrierId' => $id,
            'statutsCourrier' => $statutsCourrier
        ]);
    }
}
