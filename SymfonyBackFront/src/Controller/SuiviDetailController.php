<?php

namespace App\Controller;

use App\Entity\Courrier;
use App\Repository\CourrierRepository;
use App\Repository\StatutCourrierRepository;
use App\Repository\StatutRepository;
use App\Services\SuiviDetailService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Exception;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class SuiviDetailController extends AbstractController
{

    private $suiviDetailService;

    function __construct(SuiviDetailService $suiviDetailService){

        $this->suiviDetailService = $suiviDetailService;

    }

    #[Route('/signature', name: 'signature')]
public function saveImage(Request $request, CourrierRepository $courrierRepository): JsonResponse
{
    $imageFile = $request->files->get('image');
    $id = $request->request->get('id');

    if ($imageFile && $id) {
        $courrier = $courrierRepository->find($id);
        error_log("Le courrier est", $id);
        $courrier->setSignature($imageFile);
        try {
            $courrierRepository->add($courrier, true);
            return new JsonResponse('Signature enregistrer');
            
        } catch (Exception $e) {
            return new JsonResponse('erreur');
        }
        
        return new JsonResponse(['message' => 'Image saved successfully']);
    } else {
        return new JsonResponse(['message' => 'Image saved unsuccessfully']);
    }
}


    #[Route('/suivi/{id}', name: 'suiviId')]
    public function indexbyid(
        Courrier $id,
        Request $request,
    ): Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->suiviDetailService->SuiviDetail2($request, $id);
        
    }

    #[Route('/mettreAjourStatut', name: 'statut_add')]
    public function Update(Request $request, StatutCourrierRepository $statutCourrierRepository, StatutRepository $statutRepository, CourrierRepository $courrierRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->suiviDetailService->UpdateSuiviDetail($request);
        
    }

    #[Route('/supprimerStatut', 'delete_statut')]
    public function DeleteStatut(Request $request, StatutCourrierRepository $statutCourrierRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->suiviDetailService->DeleteSuiviDetail($request);
    }
}
