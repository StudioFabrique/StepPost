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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/*
Cette classe donne la possiblité de créer, modifier, activer et supprimer un admin.
Seul le super admin a les droits d'accès aux différentes méthodes de cette classe.
*/

#[Route('/admin', name: 'app_')]
#[IsGranted('ROLE_SUPERADMIN')]

class UserController extends AbstractController
{
    private $requestManagerService, $entityManagementService, $messageService;
    public function __construct(RequestManagerService $requestManagerService, EntityManagementService $entityManagementService, MessageService $messageService)
    {
        $this->requestManagerService = $requestManagerService;
        $this->entityManagementService = $entityManagementService;
        $this->messageService = $messageService;
    }

    /*
    Retourne un template twig avec la liste des admins avec une pagination.
    */
    #[Route('/', name: 'admin')]
    public function index(
        DataFinderService $dataFinderService,
        Request $request
    ): Response {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $data = $dataFinderService->GetAdmins();

        $dataPagination = $dataFinderService->Paginate($data, $request);

        return $this->render('admin/index.html.twig', $this->requestManagerService->GenerateRenderRequest('admin', $request, $dataPagination, $data));
    }

    /*
        La méthode new permet de créer un administrateur ayant comme rôle ROLE_ADMIN
    */
    #[Route('/ajouter', name: 'admin_add')]
    public function new(Request $request,): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UserType::class, null, ['addUser' => true])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $admin = $this->entityManagementService->MakeUser($form, $request->get('isMairie'));
                if ($request->get('isMairie'))  $this->entityManagementService->MakeRaisonSociale('mairie de pau');
                return $this->redirectToRoute('app_admin', $this->messageService->GetSuccessMessage("Administrateur", 1, $admin->getNom()));
            } catch (Exception $e) {
                return $this->redirectToRoute('app_admin', $this->messageService->GetErrorMessage("Administrateur", $e->getCode() === 3 ? 3 : 1));
            }
        }

        return $this->renderForm('admin/new.html.twig', $this->requestManagerService->GenerateRenderFormRequest('admin', $request, $form));
    }

    /*
        La méthode edit permet de modifier les informations d'un administrateur
    */
    #[Route('/edit/{id}', name: 'admin_edit')]
    public function edit(Request $request, UserRepository $adminRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $adminId = $request->get('id');
        $admin = $adminRepository->find($adminId);
        $form = $this->createForm(UserType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $admin = $this->entityManagementService->EditUser($form);
                return $this->redirectToRoute('app_admin', $this->messageService->GetSuccessMessage("Administrateur", 2, $admin->getNom()));
            } catch (Exception) {
                return $this->redirectToRoute('app_admin', $this->messageService->GetErrorMessage("Administrateur", 2, $admin->getNom()));
            }
        }

        return $this->renderForm('admin/edit.html.twig', $this->requestManagerService->GenerateRenderFormRequest('admin', $request, $form));
    }

    /*
        La méthode editPassword permet de modifier le mot de passe d'un administrateur
    */
    #[Route(name: 'edit_password', path: '/editPassword')]
    public function editPassword(Request $request, UserRepository $adminRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $adminId = $request->get('id');
        $admin = $adminRepository->find($adminId);

        $form = $this->createForm(UserType::class, null, ['editPassword' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $admin = $this->entityManagementService->EditPasswordUser($admin, $form->get("password")->getData());
                return $this->redirectToRoute('app_admin', $this->messageService->GetSuccessMessage("Administrateur", 3, $admin->getNom()));
            } catch (Exception $e) {
                return $this->redirectToRoute('app_admin', $this->messageService->GetErrorMessage("Administrateur", $e->getCode() === 3 ? 3 : 2, $admin->getNom()));
            }
        }

        return $this->renderForm('admin/edit.html.twig', $this->requestManagerService->GenerateRenderFormRequest('admin', $request, $form));
    }

    /*
        La méthode delete permet de supprimer un administrateur
    */
    #[Route('/delete/{id}', name: 'admin_delete')]
    public function delete(User $admin, UserRepository $adminRepository, ConfigAppService $config): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        try {
            if(!$config->needToBeSetup()) {
                throw new Exception();
            }
            $adminRepository->remove($admin);
            return $this->redirectToRoute('app_admin', $this->messageService->GetSuccessMessage("Administrateur", 4, $admin->getNom()));
        } catch (Exception) {
            return $this->redirectToRoute('app_admin', $this->messageService->GetErrorMessage("Administrateur", 4, $admin->getNom()));
        }
    }
}
