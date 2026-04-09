<?php

namespace App\Core;

use Firebase\JWT\JWT;

class JwtService
{
    private string $secret;
    private int $expiry;

    public function __construct()
    {
        $this->secret = (string) getenv('JWT_SECRET');
        $this->expiry = (int) (getenv('JWT_EXPIRY') ?: 3600);

        if (empty($this->secret)) {
            throw new \RuntimeException('JWT_SECRET environment variable is not set');
        }
    }

    public function generate(int $userId, string $role): string
    {
        return JWT::encode([
            'sub' => $userId,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + $this->expiry,
        ], $this->secret, 'HS256');
    }

    public function decode(string $token): object
    {
        return JWT::decode($token, $this->secret, ['HS256']);
    }
}
