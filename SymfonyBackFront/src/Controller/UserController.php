<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('/', name: 'admin')]
    public function index(
        UserRepository $userSteps,
        Request $request,
        PaginatorInterface $paginator
    ): Response {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $donner = $userSteps->findAll([], ['id' => 'DESC']);
        $userStep = $paginator->paginate(
            $donner,
            $request->query->getInt('page', 1),
            3
        );

        return $this->render('admin/index.html.twig', [
            'userStep' => $userStep
        ]);
    }

    #[Route('/ajouter', name: 'admin_add')]
    public function new(Request $request, UserRepository $userStepRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $userStep = new User();
        $form = $this->createForm(UserType::class, $userStep);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pass = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword(
                $userStep,
                $pass
            );
            $userStep->setPassword($hashedPassword);
            $userStep->setCreatedAt(new DateTime('now'));
            $userStep->setUpdatedAt(new DateTime('now'));
            $userStep->setRoles(['ROLE_ADMIN']);
            $userStepRepository->add($userStep);
            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/new.html.twig', [
            'user_step' => $userStep,
            'form' => $form,
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_edit')]
    public function edit(Request $request, User $userStep, UserRepository $userStepRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $userid = $request->get('userid');
        $form = $this->createForm(UserType::class, $userStep);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pass = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword(
                $userStep,
                $pass
            );
            $userStep->setPassword($hashedPassword);
            $userStep->setRoles(['ROLE_ADMIN']);
            $userStep->setUpdatedAt(new DateTime('now'));
            $userStepRepository->add($userStep);
            return $this->redirectToRoute('app_logout', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/edit.html.twig', [
            'user_step' => $userStep,
            'form' => $form
        ]);
    }


    #[Route('/delete/{id}', name: 'admin_delete', methods: ['POST'])]
    public function delete(Request $request, User $userStep, UserRepository $userStepRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $userStep->getId(), $request->request->get('_token'))) {
            $userStepRepository->remove($userStep);
        }

        return $this->redirectToRoute('app_logout', [], Response::HTTP_SEE_OTHER);
    }
}
