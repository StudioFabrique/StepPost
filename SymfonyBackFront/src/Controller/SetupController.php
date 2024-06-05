<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Services\ConfigAppService;
use App\Services\SetupService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/parametrage', name: 'app_')]
class SetupController extends AbstractController
{
    private $configAppService, $setupService;
    public function __construct(ConfigAppService $configAppService, SetupService $setupService)
    {
        $this->configAppService = $configAppService;
        $this->setupService = $setupService;
    }

    #[Route('/pass', name: 'checkPass')]
    public function checkPass(Request $request): Response
    {
        return $this->setupService->checkpassService($request);
    }

    #[Route('/creerAdmin', name: 'setup')]
    public function makeSuperAdmin(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $hasher)
    {
       return $this->setupService->makeSuperAdminService($request, $userRepository, $hasher);
    }
}
