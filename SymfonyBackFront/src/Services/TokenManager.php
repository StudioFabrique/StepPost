<?php

namespace App\Services;

use Firebase\JWT\JWT;

class TokenManager
{
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
