<?php

namespace App\Controller;

use App\Entity\Courrier;
use App\Repository\ExpediteurRepository;
use App\Repository\StatutCourrierRepository;
use App\Repository\StatutRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/*
Cette classe est le point d'entrée de l'application après que 
l'utilisateur (Administrateur) se soit connecté à l'application.
Le route parent est /acceuil ayant comme alias/nom app_.
Par l'intermédiaire de cette classe, l'administrateur va pouvoir gérer les différents
courriers présents dans la base données.
*/

#[Route('/accueil', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class AccueilController extends AbstractController
{
    /*
    La fonction index est le point d'entrée de la classe.
    Cette fonction affiche tous les courriers avec une pagination.
    */
    #[Route('/', name: 'accueil')]
    public function index(
        StatutCourrierRepository $statutCourrierRepo, // Le répertoire contenant un tableau de tous les courriers
        Request $request,
        PaginatorInterface $paginator, // Interface de pagination
        StatutRepository $statuts,
        ExpediteurRepository $expediteurRepository
    ): Response {

        // vérification que l'admin soit bien connecté sinon redirection vers la page de connexion
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $order = $request->get('order') ?? "DESC";
        $rechercheCourrier = $request->get('recherche') ?? null;


        if ($rechercheCourrier == null) {
            $data = $statutCourrierRepo->findCourriers($order);
        } else {
            is_numeric($rechercheCourrier) ? $data = $statutCourrierRepo->findCourriersByBordereau($rechercheCourrier)
                : (is_string($rechercheCourrier) ? $data = $statutCourrierRepo->findCourriersByNomPrenom($rechercheCourrier)
                    : $data = $statutCourrierRepo->findCourriers($order));
        }

        $courriers = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
        );

        return $this->render('accueil/index.html.twig', [
            'courriers' => $courriers,
            'statuts' => $statuts->findAll(),
            'order' => $order == "DESC" ? "ASC" : "DESC",
            'isSearching' => is_integer($rechercheCourrier) ? true : (is_string($rechercheCourrier) ? true : false),
            'expediteursInactifs' => $expediteurRepository->findAllInactive()
        ]);
    }

    /*
    La fonction indexbyid affiche les différents statuts d'un courrier dans un template.
    */

    #[Route('/suivi/{id}', name: 'suiviId')]
    public function indexbyid(
        Courrier $id,
        StatutCourrierRepository $statutsCourrierRepo,
        ExpediteurRepository $expediteurRepository
    ): Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $statutsCourrier = $statutsCourrierRepo->findBy(["courrier" => $id], ["date" => "DESC"]);

        return $this->render('suivi_detail/index.html.twig', [
            'courrierId' => $id,
            'statutsCourrier' => $statutsCourrier,
            'expediteursInactifs' => $expediteurRepository->findAllInactive()
        ]);
    }
}
