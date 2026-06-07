<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\JwtHelper;
use App\Helpers\Responder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

/**
 * Verifies the Authorization Bearer token and attaches the decoded
 * payload (user_id, role) to the request as 'jwt_user'.
 *
 * Optionally restrict to specific roles, e.g. new JwtAuthMiddleware(['client']).
 */
class JwtAuthMiddleware
{
    public function __construct(private array $allowedRoles = [])
    {
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');

        if (!$header || !preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return $this->unauthorized('Missing or malformed Authorization header.');
        }

        try {
            $payload = JwtHelper::decode($matches[1]);
        } catch (\Throwable $e) {
            return $this->unauthorized('Invalid or expired token.');
        }

        if (!empty($this->allowedRoles) && !in_array($payload['role'] ?? null, $this->allowedRoles, true)) {
            return $this->forbidden('You do not have permission to access this resource.');
        }

        $request = $request->withAttribute('jwt_user', $payload);

        return $handler->handle($request);
    }

    private function unauthorized(string $message): Response
    {
        return Responder::error(new SlimResponse(), $message, 401);
    }

    private function forbidden(string $message): Response
    {
        return Responder::error(new SlimResponse(), $message, 403);
    }
}
