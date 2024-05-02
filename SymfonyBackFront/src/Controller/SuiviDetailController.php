<?php

namespace App\Controller;

use App\Entity\Courrier;
use App\Repository\CourrierRepository;
use App\Repository\StatutCourrierRepository;
use App\Repository\StatutRepository;
use App\Services\SuiviDetailService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
        // Read the binary content of the image
        $imagePath = $imageFile->getRealPath(); // Temp file path
        $imageContent = file_get_contents($imagePath);
        $base64 = base64_encode($imageContent); // Properly encode to base64

        $courrier = $courrierRepository->find($id);
        $courrier->setSignature($base64);
        try {
            $courrierRepository->add($courrier, true);

            return new JsonResponse("la signature a bien été prise en compte");
        } catch (FileException $e) {
            return new JsonResponse(['message' => 'Erreur lors de l\'enregistrement du fichier'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
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
