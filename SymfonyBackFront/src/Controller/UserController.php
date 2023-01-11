<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Services\DataFinder;
use App\Services\EntityManagementService;
use App\Services\MessageService;
use App\Services\RequestManager;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/* 
Cette classe donne la possiblité de créer, modifier, activer et supprimer un admin.
Seul le super admin a les droits d'accès aux différentes méthodes de cette classe.
*/

#[Route('/admin', name: 'app_')]
#[IsGranted('ROLE_SUPERADMIN')]

class UserController extends AbstractController
{
    private $requestManager, $entityManagementService, $messageService;
    public function __construct(RequestManager $requestManager, EntityManagementService $entityManagementService, MessageService $messageService)
    {
        $this->requestManager = $requestManager;
        $this->entityManagementService = $entityManagementService;
        $this->messageService = $messageService;
    }

    /* 
    Retourne un template twig avec la liste des admins avec une pagination.
    */
    #[Route('/', name: 'admin')]
    public function index(
        DataFinder $dataFinder,
        Request $request
    ): Response {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $data = $dataFinder->GetAdmins();

        $dataPagination = $dataFinder->Paginate($data, $request);

        return $this->render('admin/index.html.twig', $this->requestManager->GenerateRenderRequest('admin', $request, $dataPagination, $data));
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
                if ($request->get('isMairie'))  $this->entityManagementService->MakeRaisonSociale('mairie');
                return $this->redirectToRoute('app_admin', $this->messageService->GetSuccessMessage("Administrateur", 1, $admin->getNom()), Response::HTTP_SEE_OTHER);
            } catch (Exception) {
                return $this->redirectToRoute('app_admin_add', $this->messageService->GetErrorMessage("Administrateur", 1), Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('admin/new.html.twig', $this->requestManager->GenerateRenderFormRequest('admin', $request, $form));
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
                $admin = $this->entityManagementService->EditUser($form, in_array('ROLE_SUPERADMIN', $admin->getRoles()) ? true : false);
                return $this->redirectToRoute('app_admin', $this->messageService->GetSuccessMessage("Administrateur", 2, $admin->getNom()), Response::HTTP_SEE_OTHER);
            } catch (Exception) {
                return $this->redirectToRoute('app_admin', $this->messageService->GetErrorMessage("Administrateur", 2, $admin->getNom()), Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('admin/edit.html.twig', $this->requestManager->GenerateRenderFormRequest('admin', $request, $form));
    }

    /* 
        La méthode editPassword permet de modifier le mot de passe d'un administrateur
    */
    #[Route(name: 'edit_password', path: '/editPassword')]
    public function editPassword(Request $request, UserRepository $adminRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $adminId = $request->get('id');
        $admin = $adminRepository->find($adminId);

        $form = $this->createForm(UserType::class, $admin, ['editPassword' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $admin = $this->entityManagementService->EditPasswordUser($form);
                return $this->redirectToRoute('app_admin', $this->messageService->GetSuccessMessage("Administrateur", 3, $admin->getNom()), Response::HTTP_SEE_OTHER);
            } catch (Exception) {
                return $this->redirectToRoute('app_admin', $this->messageService->GetErrorMessage("Administrateur", 3, $admin->getNom()), Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('admin/edit.html.twig', $this->requestManager->GenerateRenderFormRequest('admin', $request, $form));
    }

    /* 
        La méthode delete permet de supprimer un administrateur
    */
    #[Route('/delete/{id}', name: 'admin_delete')]
    public function delete(User $admin, UserRepository $adminRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $adminRepository->remove($admin);
            return $this->redirectToRoute('app_admin', $this->messageService->GetSuccessMessage("Administrateur", 4, $admin->getNom()), Response::HTTP_SEE_OTHER);
        } catch (Exception) {
            return $this->redirectToRoute('app_admin', $this->messageService->GetErrorMessage("Administrateur", 4, $admin->getNom()), Response::HTTP_SEE_OTHER);
        }
    }
}
