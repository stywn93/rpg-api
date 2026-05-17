<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtLibrary
{
    public const TOKEN_TTL_SECONDS = 21600;

    private $key = "0c1c0aec6371902f298b2dbf4a447c37146d422bada499d4eead3101f0a4ca18";
    private $issuer = "rumah-gizi-api";

    public function generateToken($user)
    {
        $payload = [
            'iss' => $this->issuer,
            'iat' => time(),
            'exp' => time() + self::TOKEN_TTL_SECONDS,
            'data' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];

        return JWT::encode($payload, $this->key, 'HS256');
    }

    public function validateToken($token)
    {
        return JWT::decode($token, new Key($this->key, 'HS256'));
    }
}
