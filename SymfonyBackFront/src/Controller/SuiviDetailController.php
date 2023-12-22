<?php

namespace App\Controller;

use App\Entity\Courrier;
use App\Entity\StatutCourrier;
use App\Repository\CourrierRepository;
use App\Repository\ExpediteurRepository;
use App\Repository\StatutCourrierRepository;
use App\Repository\StatutRepository;
use DateTime;
use DateTimeZone;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class SuiviDetailController extends AbstractController
{
    /*
    Retourne un template twig avec les différents statuts d'un courrier dans un template avec la possibiliter d'en ajouter ou en supprimer.
    */

    #[Route('/suivi/{id}', name: 'suiviId')]
    public function indexbyid(
        Courrier $id,
        StatutCourrierRepository $statutsCourrierRepo,
        ExpediteurRepository $expediteurRepository,
        Request $request,
        StatutRepository $statutRepository,
        CourrierRepository $courrierRepository
    ): Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $courrierId = $request->get('id');
        $courrier = $courrierRepository->find($courrierId);
        $signature = $courrier->getSignature() ?? null;
        $procuration = $courrier->getProcuration();
        $signatureBase64 = $signature != null ? base64_decode(base64_encode(stream_get_contents($signature))) : null;

        $statuts = array();
        $statutsExistants = array();
        $nomFacteur = null;

        $statutsCourrier = $statutsCourrierRepo->findBy(["courrier" => $id], ["date" => "DESC"]);

        foreach ($statutsCourrier as $statut) {
            array_push($statutsExistants, $statut->getStatut());
            $nomFacteur = $statut->getFacteur() != null ? $statut->getFacteur()->getNom() : null;
        }

        foreach ($statutRepository->findAll() as $statut) {
            if (!in_array($statut, $statutsExistants)) {
                array_push($statuts, $statut);
            }
        }

        return $this->render('suivi_detail/index.html.twig', [
            'courrierId' => $id,
            'statutsCourrier' => $statutsCourrier,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false,
            'statutsRestants' => $statuts,
            'signature' => $signatureBase64,
            'showSignature' => $signatureBase64 == null ? false : true,
            'facteur' => $nomFacteur,
            'recherche' => $request->get('recherche'),
            'dateMin' => $request->get('dateMin'),
            'dateMax' => $request->get('dateMax'),
            'procuration' => $procuration ?? null
        ]);
    }

    #[Route('/mettreAjourStatut', name: 'statut_add')]
    public function Update(Request $request, StatutCourrierRepository $statutCourrierRepository, StatutRepository $statutRepository, CourrierRepository $courrierRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $timezone = new DateTimeZone('UTC');

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Statut courrier"]["Mise à jour"];
        $messageErreur = $messages["Messages Erreurs"]["Statut courrier"]["Mise à jour"];

        $courrierId = $request->get('courrierId');
        $statutId = $request->get('statutId');

        $statuts = $courrierRepository->find($courrierId)->getStatutsCourrier();

        $lastStatutId = 0;
        foreach ($statuts as $statut) {
            $lastStatutId = $statut->getStatut()->getStatutCode() <= $lastStatutId ? $lastStatutId : $statut->getStatut()->getId();
            $facteur = $statut->getFacteur();
        }

        // if ($lastStatutId + 1 <= 7) {
        //     $statut = $statutRepository->find($lastStatutId + 1);
        // } else {
        //     return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => 'Le statut ne peut plus être mis à jour', 'isError' => true], Response::HTTP_SEE_OTHER);
        // }

        $statutCourrier = new StatutCourrier();
        $statutCourrier
            ->setCourrier($courrierRepository->find($courrierId))
            ->setStatut($statutRepository->find($statutId))
            ->setDate(new DateTime('now', $timezone))
            ->setFacteur($facteur ?? null);
        try {
            $statutCourrierRepository->add($statutCourrier, true);
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => $message], Response::HTTP_SEE_OTHER);
        } catch (Exception) {
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => $messageErreur, 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/supprimerStatut', 'delete_statut')]
    public function DeleteStatut(Request $request, StatutCourrierRepository $statutCourrierRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Statut courrier"]["Suppression"];
        $messageErreur = $messages["Messages Erreurs"]["Statut courrier"]["Suppression"];

        $courrierId = $request->get('courrierId');
        $statutId = $request->get('statutId');

        try {
            $statutCourrierRepository->remove($statutCourrierRepository->find($statutId), true);
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => $message]);
        } catch (Exception $e) {
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => $messageErreur, 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }
}
