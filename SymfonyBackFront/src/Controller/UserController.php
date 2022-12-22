<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\ExpediteurRepository;
use App\Repository\UserRepository;
use App\Services\DataFinder;
use App\Services\EntityManagementService;
use App\Services\MessageService;
use App\Services\RequestManager;
use DateTime;
use DateTimeZone;
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

    public function __construct(private RequestManager $requestManager, EntityManagementService $entityManagementService, MessageService $messageService)
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
    public function new(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UserType::class, null, ['addUser' => true])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $admin = $this->entityManagementService->MakeUser($form);
                return $this->redirectToRoute('app_admin', $this->messageService->GetSuccessMessage("Administrateur", 1, $admin->get('nom')->getData()), Response::HTTP_SEE_OTHER);
            } catch (Exception) {
                return $this->redirectToRoute('app_admin_add', $this->messageService->GetErrorMessage("Administrateur", 1, $admin->get('nom')->getData()), Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('admin/new.html.twig', $this->requestManager->GenerateRenderFormRequest('admin_add', $request, $form));
    }

    /* 
        La méthode edit permet de modifier les informations d'un administrateur
    */
    #[Route('/edit/{id}', name: 'admin_edit')]
    public function edit(Request $request, UserRepository $adminRepository, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $timezone = new DateTimeZone('UTC');

        $adminId = $request->get('id');
        $admin = $adminRepository->find($adminId);
        $isSuperAdmin = in_array('ROLE_SUPERADMIN', $admin->getRoles()) ? true : false;
        $form = $this->createForm(UserType::class, $admin);
        $form->handleRequest($request);

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Administrateur"]["Modification"];
        $messageErreur = $messages["Messages Erreurs"]["Administrateur"]["Modification"];

        if ($form->isSubmitted() && $form->isValid()) {
            $admin->setRoles($isSuperAdmin ? ['ROLE_ADMIN', 'ROLE_SUPERADMIN'] : ['ROLE_ADMIN']);
            $admin->setUpdatedAt(new DateTime('now', $timezone));

            try {
                $adminRepository->add($admin);
                return $this->redirectToRoute('app_admin', ['errorMessage' => str_replace('[nom]', $admin->getNom(), $message)], Response::HTTP_SEE_OTHER);
            } catch (Exception) {
                return $this->redirectToRoute('app_admin', ['errorMessage' => str_replace('[nom]', $admin->getNom(), $messageErreur), 'isError' => true], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('admin/edit.html.twig', [
            'user_step' => $admin,
            'form' => $form,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    /* 
        La méthode editPassword permet de modifier le mot de passe d'un administrateur
    */
    #[Route(name: 'edit_password', path: '/eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJlZ2ciOiJlYXN0ZXIgZWdnICEifQ.0kqIIgtJjrvMtQn8TI9kkxNJ4P_27h67z5rsmv_Wsws')]
    public function editPassword(Request $request, UserRepository $adminRepository, UserPasswordHasherInterface $passwordHasher, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $adminId = $request->get('id');
        $admin = $adminRepository->find($adminId);

        $form = $this->createForm(UserType::class, $admin, ['editPassword' => true]);
        $form->handleRequest($request);

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Administrateur"]["Modification"];
        $messageErreur = $messages["Messages Erreurs"]["Administrateur"]["Modification"];

        if ($form->isSubmitted() && $form->isValid()) {
            $pass = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword(
                $admin,
                $pass
            );
            $admin->setPassword($hashedPassword);
            try {
                $adminRepository->add($admin);
                return $this->redirectToRoute('app_admin', ['errorMessage' => str_replace('[nom]', $admin->getNom(), $message)], Response::HTTP_SEE_OTHER);
            } catch (Exception) {
                return $this->redirectToRoute('app_admin', ['errorMessage' => str_replace('[nom]', $admin->getNom(), $messageErreur), 'isError' => true], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('admin/edit.html.twig', [
            'user_step' => $admin,
            'form' => $form,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
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

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Administrateur"]["Suppression"];
        $messageErreur = $messages["Messages Erreurs"]["Administrateur"]["Suppression"];

        try {
            $adminRepository->remove($admin);
            return $this->redirectToRoute('app_admin', ['errorMessage' => str_replace('[nom]', $admin->getNom(), $message)], Response::HTTP_SEE_OTHER);
        } catch (Exception) {
            return $this->redirectToRoute('app_admin', ['errorMessage' => str_replace('[nom]', $admin->getNom(), $messageErreur), 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }
}
