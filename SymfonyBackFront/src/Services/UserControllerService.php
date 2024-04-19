<?php

namespace App\Services;

use Exception;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Services\EntityManagementService;
use App\Services\MessageService;
use App\Services\RequestManagerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserControllerService extends AbstractController{

    private $requestManagerService, $entityManagementService, $messageService;

    function _construct(RequestManagerService $requestManagerService, EntityManagementService $entityManagementService, MessageService $messageService){
        $this->requestManagerService = $requestManagerService;
        $this->entityManagementService = $entityManagementService;
        $this->messageService = $messageService;
    }

    /*
        La méthode delete permet de supprimer un administrateur
    */

    function deleteUserControllerService(User $admin, UserRepository $adminRepository):Response {   

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        try {
            if(in_array("ROLE_SUPERADMIN",$admin->getRoles())) {
                throw new Exception();
            }
            $adminRepository->remove($admin);
            return $this->redirectToRoute('app_admin', $this->messageService->GetSuccessMessage("Administrateur", 4, $admin->getNom()));
        } catch (Exception) {
            return $this->redirectToRoute('app_admin', $this->messageService->GetErrorMessage("Administrateur", 4, $admin->getNom()));
        }

    }
}