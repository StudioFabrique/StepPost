<?php

namespace App\Services;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ConfigAppService extends AbstractController
{
    private $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function needToBeSetup(): bool
    {
        return
            count($this->userRepository->findAll()) > 0
            ? false
            : true;
    }

    public function setupApp(): RedirectResponse
    {
        return $this->redirectToRoute("app_checkPass");
    }

    public function checkPass(Request $request): bool
    {
        if ($request->request->get("pass") == $_ENV["SETUP_PASS"]) {
            return true;
        } else {
            return false;
        }
    }
}
