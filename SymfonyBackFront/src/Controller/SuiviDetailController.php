<?php

namespace App\Controller;

use App\Entity\Courrier;
use App\Repository\CourrierRepository;
use App\Repository\StatutCourrierRepository;
use App\Repository\StatutRepository;
use App\Services\EncryptionServiceVersion2;
use App\Services\SuiviDetailService;
use Doctrine\ORM\EntityManagerInterface;
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

    private $suiviDetailService, $encryptionServiceVersion2;

    function __construct(SuiviDetailService $suiviDetailService, EncryptionServiceVersion2 $encryptionServiceVersion2){

        $this->encryptionServiceVersion2 = $encryptionServiceVersion2;
        $this->suiviDetailService = $suiviDetailService;
        

    }

    #[Route('/download/{id}', name: 'download_image')]
    public function downloadImage($id, EntityManagerInterface $entityManager): Response
    {
        
        $courrier = $entityManager->getRepository(Courrier::class)->find($id);

        $encryptedBlob = stream_get_contents($courrier->getSignature());

        $blob = $this->encryptionServiceVersion2->encrypt_decrypt('decrypt',$encryptedBlob);

        if ($encryptedBlob === false) {
            throw new \Exception('Déchiffrement échoué');
        }

        $response = new Response($blob);
        $response->headers->set('Content-Type', 'image/png'); 
        $response->headers->set('Content-Disposition', 'inline; filename="image.png"');

        return $response;
    }

    #[Route('/upload/{id}', name: 'upload_image')]
    public function uploadImage(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $file = $request->files->get('file');
        if ($file) {
            try {
                $blob = file_get_contents($file->getPathname());

                $encryptedBlob = $this->encryptionServiceVersion2->encrypt_decrypt('encrypt',$blob);
                
                $courrier = $entityManager->getRepository(Courrier::class)->find($id);
                if (!$courrier) {
                    return new JsonResponse(['success' => false, 'error' => 'Courrier not found.']);
                }

                
                $courrier->setSignature($encryptedBlob);
                $entityManager->persist($courrier);
                $entityManager->flush();

                return new JsonResponse(['success' => true]);
            } catch (\Exception $e) {
                return new JsonResponse(['success' => false, 'error' => $e->getMessage()]);
            }
        }

        return new JsonResponse(['success' => false, 'error' => 'No file uploaded.']);
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
    #[Route('/supprimerSignature', 'delete_signature')]
    public function DeleteSignature(Request $request):Response
    {
        return $this->suiviDetailService->DeleteSignature($request);
    }
}
