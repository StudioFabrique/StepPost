<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Services\ConfigAppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/parametrage', name: 'app_')]
class SetupController extends AbstractController
{
    private $configAppService;
    public function __construct(ConfigAppService $configAppService)
    {
        $this->configAppService = $configAppService;
    }

    #[Route('/pass', name: 'checkPass')]
    public function checkPass(Request $request): Response
    {
        if (!$this->configAppService->needToBeSetup()) {
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod("POST")) {
            if ($this->configAppService->checkPass($request)) {
                return $this->redirectToRoute("app_setup", ['pass' => $_ENV["SETUP_PASS"]]);
            } else {
                return $this->redirectToRoute("app_login");
            }
        }
        return $this->render('setup/index.html.twig', ['errorMessage' => null, 'expediteursInactifs' => null, 'form' => null]);
    }

    #[Route('/creerAdmin', name: 'setup')]
    public function makeSuperAdmin(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $hasher)
    {
        if ($request->get('pass') != $_ENV["SETUP_PASS"] || !$this->configAppService->needToBeSetup()) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UserType::class, null, [
            "addUser" => true
        ])->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();
            $password = $hasher->hashPassword($user, $form->get("password")->getData());

            $user
                ->setEmail($form->get("email")->getData())
                ->setNom($form->get("nom")->getData())
                ->setFonction($form->get("fonction")->getData())
                ->setRoles(["ROLE_ADMIN", "ROLE_GESTION", "ROLE_SUPERADMIN"])
                ->setPassword($password)
                ->setCreatedAt(new DateTime('now'))
                ->setUpdatedAt(new DateTime('now'));

            if (!$this->configAppService->needToBeSetup()) {
                return $this->redirectToRoute("app_login");
            } else {
                try {
                    $userRepository->add($user);
                    return $this->redirectToRoute("app_login");
                } catch (Exception) {
                    return $this->redirectToRoute("app_login");
                }
            }
        }
        return $this->renderForm(
            'setup/index.html.twig',
            [
                'form' => $form,
                'errorMessage' => null,
                'expediteursInactifs' => null
            ]
        );
    }
}
