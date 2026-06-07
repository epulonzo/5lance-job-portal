<?php

declare(strict_types=1);

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface as Response;

class Responder
{
    public static function json(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    public static function error(Response $response, string $message, int $status = 400, array $errors = []): Response
    {
        $payload = ['error' => $message];
        if (!empty($errors)) {
            $payload['details'] = $errors;
        }

        return self::json($response, $payload, $status);
    }
}
