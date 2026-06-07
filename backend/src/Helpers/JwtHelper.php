<?php

declare(strict_types=1);

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper
{
    public static function encode(array $payload): string
    {
        $secret = $_ENV['JWT_SECRET'];
        $expiry = (int) ($_ENV['JWT_EXPIRY'] ?? 86400);
        $now = time();

        $claims = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $expiry,
        ]);

        return JWT::encode($claims, $secret, 'HS256');
    }

    /**
     * @throws \Firebase\JWT\ExpiredException|\UnexpectedValueException on invalid/expired token
     */
    public static function decode(string $token): array
    {
        $secret = $_ENV['JWT_SECRET'];
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));

        return (array) $decoded;
    }
}
