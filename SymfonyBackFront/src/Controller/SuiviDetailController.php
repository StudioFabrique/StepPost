<?php

namespace App\Controller;

use App\Entity\Courrier;
use App\Entity\Statut;
use App\Entity\StatutCourrier;
use App\Repository\CourrierRepository;
use App\Repository\ExpediteurRepository;
use App\Repository\StatutCourrierRepository;
use App\Repository\StatutRepository;
use DateTime;
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
    La fonction indexbyid affiche les différents statuts d'un courrier dans un template.
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
        $signature = $courrierRepository->find($courrierId)->getSignature() ?? null;
        $signatureBase64 = $signature != null ? base64_decode(base64_encode(stream_get_contents($signature))) : null;

        $statuts = array();
        $statutsExistants = array();

        $statutsCourrier = $statutsCourrierRepo->findBy(["courrier" => $id], ["date" => "DESC"]);

        foreach ($statutsCourrier as $statut) {
            array_push($statutsExistants, $statut->getStatut());
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
            'showSignature' => $signatureBase64 == null ? false : true
        ]);
    }

    #[Route('/mettreAjourStatut', name: 'statut_add')]

    public function Update(Request $request, StatutCourrierRepository $statutCourrierRepository, StatutRepository $statutRepository, CourrierRepository $courrierRepository): Response
    {
        $courrierId = $request->get('courrierId');
        $statutId = $request->get('statutId');

        $statuts = $courrierRepository->find($courrierId)->getStatutsCourrier();

        $lastStatutId = 0;
        foreach ($statuts as $statut) {
            $lastStatutId = $statut->getStatut()->getId() <= $lastStatutId ? $lastStatutId : $statut->getStatut()->getId();
            $facteur = $statut->getFacteur();
        }

        if ($lastStatutId + 1 <= 7) {
            $statut = $statutRepository->find($lastStatutId + 1);
        } else {
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => 'Le statut ne peut plus être mis à jour', 'isError' => true], Response::HTTP_SEE_OTHER);
        }

        $statutCourrier = new StatutCourrier();
        $statutCourrier
            ->setCourrier($courrierRepository->find($courrierId))
            ->setStatut($statutRepository->find($statutId))
            ->setDate(new DateTime('now'))
            ->setFacteur($facteur);
        try {
            $statutCourrierRepository->add($statutCourrier, true);
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => 'Le statut a été mis à jour'], Response::HTTP_SEE_OTHER);
        } catch (Exception) {
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => 'Impossible de mettre le statut à jour', 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/supprimerStatut', 'delete_statut')]
    public function DeleteStatut(Request $request, StatutCourrierRepository $statutCourrierRepository): Response
    {
        $courrierId = $request->get('courrierId');
        $statutId = $request->get('statutId');

        try {
            $statutCourrierRepository->remove($statutCourrierRepository->find($statutId), true);
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => 'Le statut a bien été supprimé']);
        } catch (Exception $e) {
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => 'Impossible de supprimer le statut', 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }
}
