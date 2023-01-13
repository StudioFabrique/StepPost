<?php

namespace App\Services;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Service contenant des méthodes pour la configuration initiale
 * de l'application (quand aucun admin n'a été crée)
 */
class ConfigAppService
{
    private $userRepository;
    /**
     * Constructeur
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Compte le nombre d'utilisateurs inscrits afin de déterminer si
     * une configuration initiale de l'appli est nécessaire.
     */
    public function needToBeSetup(): bool
    {
        return
            count($this->userRepository->findAll()) > 0
            ? false
            : true;
    }

    /**
     * Vérifie que le mot de passe de configuration de l'application
     * dans le fichier de variables d'environnement soit le même
     * rentré par l'utilisateur non authentifié.
     */
    public function checkPass(Request $request): bool
    {
        if ($request->request->get("pass") == $_ENV["SETUP_PASS"]) {
            return true;
        } else {
            return false;
        }
    }
}
