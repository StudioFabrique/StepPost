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

#[Route('/admin', name: 'app_')]
#[IsGranted('ROLE_SUPERADMIN')]
class AdminController extends AbstractController
{
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

        $donner = $admins->findAll([], ['id' => 'DESC']);
        $admins = $paginator->paginate(
            $donner,
            $request->query->getInt('page', 1),
            3
        );

        return $this->render('admin/index.html.twig', [
            'admins' => $admins,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    #[Route('/ajouter', name: 'admin_add')]
    public function new(Request $request, UserRepository $adminRepository, UserPasswordHasherInterface $passwordHasher, ExpediteurRepository $expediteurRepository): Response
    {
        $admin = new User();
        $form = $this->createForm(UserType::class, $admin);
        $form->handleRequest($request);

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
                return $this->redirectToRoute('app_admin', ['errorMessage' => "L'administrateur "  . $form->get('nom')->getData() . " a été créé"], Response::HTTP_SEE_OTHER);
            } catch (Exception) {
                return $this->redirectToRoute('app_admin_add', ['errorMessage' => "L'adresse mail saisie est déjà associée à un administrateur existant", 'isError' => true], Response::HTTP_SEE_OTHER);
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
        $adminId = $request->get('id');
        $admin = $adminRepository->find($adminId);
        $isSuperAdmin = in_array('ROLE_SUPERADMIN', $admin->getRoles()) ? true : false;
        $form = $this->createForm(UserType::class, $admin);
        $form->handleRequest($request);

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
                return $this->redirectToRoute('app_admin', ['errorMessage' => "L'administrateur " . $form->get('nom')->getData() . " a été modifié"], Response::HTTP_SEE_OTHER);
            } catch (Exception) {
                return $this->redirectToRoute('app_admin', ['errorMessage' => "L'email saisie est déjà attribuée à un autre administrateur", 'isError' => true], Response::HTTP_SEE_OTHER);
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


    #[Route('/delete/{id}', name: 'admin_delete', methods: ['POST'])]
    public function delete(User $admin, UserRepository $adminRepository): Response
    {
        try {
            $adminRepository->remove($admin);
            return $this->redirectToRoute('app_admin', ['errorMessage' => "L'administrateur " . $admin->getNom() . " a été supprimé"], Response::HTTP_SEE_OTHER);
        } catch (Exception) {
            return $this->redirectToRoute('app_admin', ['errorMessage' => "L'administrateur " . $admin->getNom() . " n'a pas pu être supprimé", 'isError' => true], Response::HTTP_SEE_OTHER);
        }
    }
}
