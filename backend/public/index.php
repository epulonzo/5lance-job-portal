<?php

declare(strict_types=1);

use App\Controllers\ApplicationController;
use App\Controllers\AuthController;
use App\Controllers\JobController;
use App\Controllers\UserController;
use App\Helpers\Responder;
use App\Middleware\JwtAuthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Automatic DB Migration for users table new columns
try {
    $db = App\Helpers\Database::getConnection();
    $check = $db->query("SHOW COLUMNS FROM users LIKE 'title'");
    if (!$check->fetch()) {
        $db->exec("ALTER TABLE users 
            ADD COLUMN title VARCHAR(150) NULL, 
            ADD COLUMN location VARCHAR(100) NULL, 
            ADD COLUMN resume_url VARCHAR(255) NULL");
    }
} catch (Throwable $e) {
    // Suppress startup migration errors
}

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// CORS — allow the Vue SPA to call this API from a different origin
$app->add(function (Request $request, $handler): Response {
    $response = $handler->handle($request);
    $origin = $_ENV['CORS_ORIGIN'] ?? '*';

    return $response
        ->withHeader('Access-Control-Allow-Origin', $origin)
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

$errorMiddleware = $app->addErrorMiddleware(
    (bool) ($_ENV['APP_ENV'] ?? 'development') === 'development',
    true,
    true
);
$errorMiddleware->setDefaultErrorHandler(function (
    Request $request,
    Throwable $exception
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();
    $status = $exception instanceof \Slim\Exception\HttpException ? $exception->getCode() : 500;

    return Responder::error($response, $exception->getMessage(), $status ?: 500);
});

$app->get('/', function (Request $request, Response $response) {
    return Responder::json($response, ['message' => '5Lance API is running.']);
});

$app->group('/api', function (RouteCollectorProxy $group) {
    // Auth
    $group->post('/auth/register', [AuthController::class, 'register']);
    $group->post('/auth/login', [AuthController::class, 'login']);

    // Users
    $group->get('/users', [UserController::class, 'index'])
        ->add(new JwtAuthMiddleware(['admin']));
    $group->get('/users/{id}', [UserController::class, 'show'])
        ->add(new JwtAuthMiddleware());
    $group->post('/users/{id}', [UserController::class, 'update'])
        ->add(new JwtAuthMiddleware());
    $group->delete('/users/{id}', [UserController::class, 'delete'])
        ->add(new JwtAuthMiddleware(['admin']));

    // Jobs
    $group->get('/jobs', [JobController::class, 'index']);
    $group->get('/jobs/{id}', [JobController::class, 'show']);
    $group->post('/jobs', [JobController::class, 'create'])
        ->add(new JwtAuthMiddleware(['client']));
    $group->put('/jobs/{id}', [JobController::class, 'update'])
        ->add(new JwtAuthMiddleware(['client']));
    $group->delete('/jobs/{id}', [JobController::class, 'delete'])
        ->add(new JwtAuthMiddleware(['client', 'admin']));

    // Applications
    $group->get('/applications', [ApplicationController::class, 'index'])
        ->add(new JwtAuthMiddleware());
    $group->get('/jobs/{id}/applications', [ApplicationController::class, 'indexForJob'])
        ->add(new JwtAuthMiddleware(['client']));
    $group->post('/jobs/{id}/applications', [ApplicationController::class, 'create'])
        ->add(new JwtAuthMiddleware(['freelancer']));
    $group->put('/applications/{id}', [ApplicationController::class, 'updateStatus'])
        ->add(new JwtAuthMiddleware(['client']));
    $group->delete('/applications/{id}', [ApplicationController::class, 'withdraw'])
        ->add(new JwtAuthMiddleware(['freelancer']));
});

$app->run();
