<?php

namespace App\Controller;

use App\Entity\Expediteur;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/* 
Classe test pour générer un token en retournant une réponse JSON.
Pour éviter l'erreur "Unable to create a signed JWT from the given configuration",
il faut utiliser la commande :
lexik:jwt:generate-keypair --overwrite 
*/

class TestTokenController extends AbstractController
{
    #[Route('/testtoken', name: 'app_testtoken')]
    public function showToken(JWTTokenManagerInterface $jwt): Response
    {
        $expediteur = new Expediteur();
        $jwt->create($expediteur);
        return new JsonResponse(['token' => $jwt]);
    }
}
