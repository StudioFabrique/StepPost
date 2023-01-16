<?php

namespace App\Services;

use Firebase\JWT\JWT;

/**
 * Service pour la gestion de token
 */
class TokenManager
{
    /**
     * Génère un token en fonction des données et du temps d'expiration
     */
    public function generateToken(array $data, int $expirationHours): string
    {
        $token = (new JWT())->encode(
            $data,
            $_ENV['PASS_PHRASE'], // pass phrase
            'HS256', // protocole d'encodage
            head: ['exp' => time() + (3600 * $expirationHours)]
        );
        return $token;
    }
}
