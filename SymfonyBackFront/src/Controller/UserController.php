<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Services\ConfigAppService;
use App\Services\DataFinderService;
use App\Services\EntityManagementService;
use App\Services\MessageService;
use App\Services\RequestManagerService;
use App\Services\UserControllerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/*
Cette classe donne la possiblité de créer, modifier, activer et supprimer un admin.
Seul le super admin a les droits d'accès aux différentes méthodes de cette classe.
*/

#[Route('/admin', name: 'app_')]
#[IsGranted('ROLE_SUPERADMIN')]

class UserController extends AbstractController
{
    private $requestManagerService, $entityManagementService, $messageService, $validator, $userControllerService;
    public function __construct(RequestManagerService $requestManagerService, EntityManagementService $entityManagementService, MessageService $messageService, ValidatorInterface $validator, UserControllerService $userControllerService)
    {
        $this->requestManagerService = $requestManagerService;
        $this->entityManagementService = $entityManagementService;
        $this->messageService = $messageService;
        $this->validator = $validator;
        $this->userControllerService = $userControllerService;
    }

    #[Route('/', name: 'admin')]
    public function index(DataFinderService $dataFinderService, Request $request ): Response {
        return $this->userControllerService->indexService($dataFinderService, $request);
 }

    #[Route('/ajouter', name: 'admin_add')]
    public function new(Request $request,): Response
    {
        return $this->userControllerService->newUserControllerService($request);
    }

   
    #[Route('/edit/{id}', name: 'admin_edit')]
    public function edit(Request $request, UserRepository $adminRepository): Response
    {
        return $this->userControllerService->editUserControllerService($request, $adminRepository);
    }

    
    #[Route(name: 'edit_password', path: '/editPassword')]
    public function editPassword(Request $request, UserRepository $adminRepository): Response
    {
        return $this->userControllerService->editPasswordUserControllerService($request, $adminRepository);
    }

    
    #[Route('/delete/{id}', name: 'admin_delete')]
    public function delete(User $admin, UserRepository $adminRepository): Response
    {
        return $this->userControllerService->deleteUserControllerService($admin, $adminRepository);
    }
}
