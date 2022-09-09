<?php

namespace App\Controller;

use App\Entity\Expediteur;
use App\Form\ExpediteurType;
use App\Repository\ExpediteurRepository;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/utilisateur', name: 'app_')]
//#[IsGranted('ROLE_ADMIN')]
class ClientController extends AbstractController
{
    #[Route('/', name: 'utilisateur', methods: ['GET'])]
    public function index(
        ExpediteurRepository $expediteurs,
        Request $request,
        PaginatorInterface $paginator
    ): Response {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $donner = $expediteurs->findAll([], ['id' => 'DESC']);
        $expediteur = $paginator->paginate(
            $donner,
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('utilisateur/index.html.twig', [
            'utilisateur' => $expediteur,
        ]);
    }



    #[Route('/ajouter', name: 'add')]
    public function new(Request $request): Response
    {
        $expediteur = new Expediteur();
        $form = $this->createForm(ExpediteurType::class, $expediteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $token = (new JWT())->encode(
                [
                    'email' => $form->get('email')->getData(),
                    'nom' => $form->get('nom')->getData(),
                    'exp' => time() + (3600 * 24)
                ],
                'jdd23mnj6n2mn42mtoto',
                'HS256'
            );
            return $this->redirectToRoute('app_token', ['token' => $token]);
        }

        return $this->renderForm('utilisateur/new.html.twig', [
            'expediteur' => $expediteur,
            'form' => $form
        ]);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, Expediteur $expediteur, ExpediteurRepository $expediteurRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(ExpediteurType::class, $expediteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $expediteurRepository->add($expediteur);
            return $this->redirectToRoute('app_utilisateur', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('utilisateur/edit.html.twig', [
            'expediteur' => $expediteur,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Expediteur $expediteur, ExpediteurRepository $expediteurRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $expediteur->getId(), $request->request->get('_token'))) {
            $expediteurRepository->remove($expediteur);
        }

        return $this->redirectToRoute('app_utilisateur', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/mailToken', name: 'token')]
    public function RedirectTokenMailView(Request $request)
    {
        $token = $request->get('token');
        return $this->render('client/tokenMailRedirect.html.twig', [
            'token' => $token
        ]);
    }
}
