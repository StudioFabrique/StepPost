<?php

namespace App\Services;

use DateTimeZone;
use App\Entity\Courrier;
use DateTime;
use Exception;
use App\Entity\StatutCourrier;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\StatutRepository;
use App\Repository\ExpediteurRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\CourrierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\StatutCourrierRepository;


class SuiviDetailService extends AbstractController{

    private $statutRepository, $statutcourrierrepository, $courrierRepository, $statutsCourrierRepo, $expediteurRepository;

    public function __construct(
    ExpediteurRepository $expediteurRepository,
    CourrierRepository $courrierRepository, 
    StatutRepository $statutRepository, 
    StatutCourrierRepository $statutCourrierRepository,
    StatutCourrierRepository $statutsCourrierRepo)
    {
        
        $this->courrierRepository = $courrierRepository;
        $this->expediteurRepository = $expediteurRepository;
        $this->statutRepository = $statutRepository;
        $this->statutsCourrierRepo = $statutsCourrierRepo;
        $this->statutcourrierrepository = $statutCourrierRepository;

    }

    //Ceci va permettre d'update (mettre à jour) le statut d'un courrier

    function UpdateSuiviDetail(Request $request): Response{
        $timezone = new DateTimeZone('UTC');

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Statut courrier"]["1,Mise à jour"];
        $messageErreur = $messages["Messages Erreurs"]["Statut courrier"]["1,Mise à jour"];

        $courrierId = $request->get('courrierId');
        $statutId = $request->get('statutId');

        $statuts = $this->courrierRepository->find($courrierId)->getStatutsCourrier();

        $lastStatutId = 0;
        foreach ($statuts as $statut) {
            $lastStatutId = $statut->getStatut()->getStatutCode() <= $lastStatutId ? $lastStatutId : $statut->getStatut()->getId();
            $facteur = $statut->getFacteur();
        }

        $statutCourrier = new StatutCourrier();
        $statutCourrier
            ->setCourrier($this->courrierRepository->find($courrierId))
            ->setStatut($this->statutRepository->find($statutId))
            ->setDate(new DateTime('now', $timezone))
            ->setFacteur($facteur ?? null);
        try {
            $this->statutcourrierrepository->add($statutCourrier, true);
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => $message], Response::HTTP_SEE_OTHER);
        } catch (Exception) {
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => $messageErreur, 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }

    //Ici, on va supprimer le statut de celui-ci 

    function DeleteSuiviDetail($request){
        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Statut courrier"]["2,Suppression"];
        $messageErreur = $messages["Messages Erreurs"]["Statut courrier"]["2,Suppression"];

        $courrierId = $request->get('courrierId');
        $statutId = $request->get('statutId');

        try {
            $this->statutcourrierrepository->remove($this->statutcourrierrepository->find($statutId), true);
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => $message]);
        } catch (Exception $e) {
            return $this->redirectToRoute('app_suiviId', ['id' => $courrierId, 'errorMessage' => $messageErreur, 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }

    /*
    Retourne un template twig avec les différents statuts d'un courrier dans un template avec la possibiliter d'en ajouter ou en supprimer.
    */

    function SuiviDetail2($request, $id){
        $courrierId = $request->get('id');
        $courrier = $this->courrierRepository->find($courrierId);

        $signature = $courrier->getSignature(); // Supposant que c'est déjà une chaîne Base64
    
        $procuration = $courrier->getProcuration();
    
       
        $signatureBase64 = $signature != null ? base64_decode(base64_encode(stream_get_contents($signature))) : null;
     
        $statuts = array();
        $statutsExistants = array();
        $nomFacteur = null;
        
        $statutsCourrier = $this->statutsCourrierRepo->findBy(["courrier" => $id], ["date" => "DESC"]);

        foreach ($statutsCourrier as $statut) {
            array_push($statutsExistants, $statut->getStatut());
            $nomFacteur = $statut->getFacteur() != null ? $statut->getFacteur()->getNom() : null;
        }

        foreach ($this->statutRepository->findAll() as $statut) {
            if (!in_array($statut, $statutsExistants)) {
                array_push($statuts, $statut);
            }
        }
        
        return $this->render('suivi_detail/index.html.twig', [
            'courrierId' => $id,
            'statutsCourrier' => $statutsCourrier,
            'expediteursInactifs' => $this->expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false,
            'statutsRestants' => $statuts,
            'signature' => $signatureBase64 ? 'data:image/png;base64,' . $signatureBase64 : null,
            'showSignature' => $signatureBase64 == null ? false : true,
            'facteur' => $nomFacteur,
            'recherche' => $request->get('recherche'),
            'dateMin' => $request->get('dateMin'),
            'dateMax' => $request->get('dateMax'),
            'procuration' => $procuration ?? null
        ]);
    }

}