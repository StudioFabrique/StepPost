<?php

namespace App\Controller;

use App\Repository\ExpediteurRepository;
use App\Repository\StatutCourrierRepository;
use App\Repository\StatutRepository;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use League\Csv\Writer;
use League\Csv\CannotInsertRecord;

/*
Cette classe est le point d'entrée de l'application après que 
l'utilisateur (Administrateur) se soit connecté à l'application.
Par l'intermédiaire de cette classe, l'administrateur va pouvoir gérer les différents
courriers présents dans la base données.
*/

#[Route('/accueil', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class AccueilController extends AbstractController
{
    /*
    Cette méthode affiche tous les courriers avec une pagination.
    */
    #[Route('/', name: 'accueil')]
    public function index(
        StatutCourrierRepository $statutCourrierRepo, // Le répertoire contenant un tableau de tous les courriers
        Request $request,
        PaginatorInterface $paginator, // Interface de pagination
        StatutRepository $statuts,
        ExpediteurRepository $expediteurRepository,
    ): Response {

        // vérification que l'admin soit bien connecté sinon redirection vers la page de connexion
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $order = $request->get('order') ?? "DESC";
        $rechercheCourrier = $request->get('recherche') ?? null;
        $currentPage = $request->get('currentPage') ?? 1;

        if ($rechercheCourrier == null) {
            $data = $statutCourrierRepo->findCourriers($order);
        } else {
            is_numeric($rechercheCourrier) ? $data = $statutCourrierRepo->findCourriersByBordereau($rechercheCourrier)
                : (is_string($rechercheCourrier) ? $data = $statutCourrierRepo->findCourriersByNomPrenom($rechercheCourrier)
                    : $data = $statutCourrierRepo->findCourriers($order));
        }

        $courriers = $paginator->paginate(
            $data,
            $request->query->getInt('page') < 2 ? $currentPage : $request->query->getInt('page')
        );


        $csvCourriers[0] = ['Date', 'Expéditeur', 'Statut', 'Bordereau', 'Type', 'Nom', 'Prénom', 'Adresse', 'Code Postal', 'Ville'];
        $i = 1;
        foreach ($courriers as $courrier) {
            $csvCourriers[$i] = [$courrier['date'], $courrier['nomExpediteur'], $courrier['statut'], $courrier['bordereau'], $courrier['type'], $courrier['nom'], $courrier['prenom'], $courrier['adresse'], $courrier['codePostal'], $courrier['ville']];
            $i++;
        }

        try {
            $writer = Writer::createFromPath('/home/martin/Téléchargements/courriers-' . date_format(new DateTime('now'), 'h:m') . '.csv', 'w');
            $writer->insertAll($csvCourriers);
        } catch (CannotInsertRecord $e) {
            $e->getRecord();
        }

        return $this->render('accueil/index.html.twig', [
            'courriers' => $courriers,
            'statuts' => $statuts->findAll(),
            'order' => $order == "DESC" ? "ASC" : "DESC",
            'isSearching' => is_integer($rechercheCourrier) ? true : (is_string($rechercheCourrier) ? true : false),
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'nbCourriersTotal' => count($data),
            'currentPage' => $request->query->getInt('page') > 1 ? $request->query->getInt('page') <= 2 : $currentPage,
            'errorMessage' => $request->get('errorMessage') ?? null,
        ]);
    }

    #[Route('/', name: 'export_csv')]
    public function exportCsv(Request $request)
    {
        $data = $request->get('data');
        $path = '/home/martin/Téléchargements/courriers-' . date_format(new DateTime('now'), 'h:m') . '.csv';
        $csvCourriers[0] = ['Date', 'Expéditeur', 'Statut', 'Bordereau', 'Type', 'Nom', 'Prénom', 'Adresse', 'Code Postal', 'Ville'];
        $i = 1;
        foreach ($data as $courrier) {
            $csvCourriers[$i] = [
                $courrier['date'],
                $courrier['nomExpediteur'],
                $courrier['statut'], $courrier['bordereau'],
                $courrier['type'], $courrier['nom'], $courrier['prenom'],
                $courrier['adresse'],
                $courrier['codePostal'],
                $courrier['ville']
            ];
            $i++;
        }

        try {
            $writer = Writer::createFromPath($path, 'w');
            $writer->insertAll($csvCourriers);
            return $this->redirectToRoute('app_accueil', ['errorMessage' => 'Le fichier a bien été exporté' . ' au répertoire ' . $path]);
        } catch (CannotInsertRecord $e) {
            $e->getRecord();
            return $this->redirectToRoute('app_admin_add', ['errorMessage' => "L'exportation en .csv a échoué", 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }
}
