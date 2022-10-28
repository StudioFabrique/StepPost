<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\ExpediteurRepository;
use App\Repository\UserRepository;
use DateTime;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

/* 
Cette classe donne la possiblité de créer, modifier, activer et supprimer un admin.
Seul le super admin a les droits d'accès aux différentes méthodes de cette classe.
*/

#[Route('/admin', name: 'app_')]
#[IsGranted('ROLE_SUPERADMIN')]
class AdminController extends AbstractController
{

    /* 
    La méthode index affiche la liste des admins avec une pagination.
    */
    #[Route('/', name: 'admin')]
    public function index(
        UserRepository $admins,
        Request $request,
        PaginatorInterface $paginator,
        ExpediteurRepository $expediteurRepository
    ): Response {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $currentPage = $request->get('currentPage') ?? 1;

        $data = $admins->findAll([], ['id' => 'DESC']);
        $admins = $paginator->paginate(
            $data,
            $request->query->getInt('page') < 2 ? $currentPage : $request->query->getInt('page')
        );

        return $this->render('admin/index.html.twig', [
            'admins' => $admins,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false,
            'currentPage' => $request->query->getInt('page') > 1 ? $request->query->getInt('page') <= 2 : $currentPage,
            'nbAdminsTotal' => count($data)
        ]);
    }

    #[Route('/ajouter', name: 'admin_add')]
    public function new(Request $request, UserRepository $adminRepository, UserPasswordHasherInterface $passwordHasher, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $admin = new User();
        $form = $this->createForm(UserType::class, $admin);
        $form->handleRequest($request);

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Administrateur"]["Création"];
        $messageErreur = $messages["Messages Erreurs"]["Administrateur"]["Création"];

        if ($form->isSubmitted() && $form->isValid()) {
            $pass = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword(
                $admin,
                $pass
            );
            $admin->setPassword($hashedPassword);
            $admin->setCreatedAt(new DateTime('now'));
            $admin->setUpdatedAt(new DateTime('now'));
            $admin->setRoles(['ROLE_ADMIN']);
            try {
                $adminRepository->add($admin);
                return $this->redirectToRoute('app_admin', ['errorMessage' => str_replace('[nom]', $admin->getNom(), $message)], Response::HTTP_SEE_OTHER);
            } catch (Exception) {
                return $this->redirectToRoute('app_admin_add', ['errorMessage' => str_replace('[nom]', $admin->getNom(), $messageErreur), 'isError' => true], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('admin/new.html.twig', [
            'user_step' => $admin,
            'form' => $form,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_edit')]
    public function edit(Request $request, UserRepository $adminRepository, UserPasswordHasherInterface $passwordHasher, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $adminId = $request->get('id');
        $admin = $adminRepository->find($adminId);
        $isSuperAdmin = in_array('ROLE_SUPERADMIN', $admin->getRoles()) ? true : false;
        $form = $this->createForm(UserType::class, $admin);
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
            $admin->setRoles($isSuperAdmin ? ['ROLE_ADMIN', 'ROLE_SUPERADMIN'] : ['ROLE_ADMIN']);
            $admin->setUpdatedAt(new DateTime('now'));

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
