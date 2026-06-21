<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Database;
use App\Helpers\Responder;
use App\Helpers\Validator;
use App\Helpers\JwtHelper;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** POST /api/auth/register — register a new user */
    public function register(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();

        $validator = (new Validator($data))
            ->required('name', 'Name')->maxLength('name', 100)
            ->required('email', 'Email')->maxLength('email', 150)->email('email')
            ->required('password', 'Password')->maxLength('password', 255)
            ->required('role', 'Role')->in('role', ['freelancer', 'client']);

        if ($validator->fails()) {
            return Responder::error($response, 'Validation failed.', 422, $validator->errors());
        }

        // Check if email already exists
        $stmt = $this->db->prepare('SELECT user_id FROM users WHERE email = :email');
        $stmt->execute(['email' => $data['email']]);
        if ($stmt->fetch()) {
            return Responder::error($response, 'The email is already registered.', 409);
        }

        // Insert new user
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, role, bio, skills)
             VALUES (:name, :email, :password_hash, :role, :bio, :skills)'
        );
        $stmt->execute([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password_hash' => $passwordHash,
            'role'          => $data['role'],
            'bio'           => $data['bio'] ?? null,
            'skills'        => $data['skills'] ?? null,
        ]);

        $userId = (int) $this->db->lastInsertId();

        // Generate JWT token
        $token = JwtHelper::encode([
            'user_id' => $userId,
            'role'    => $data['role'],
        ]);

        $user = $this->findUser($userId);

        return Responder::json($response, [
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    /** POST /api/auth/login — authenticate and return JWT */
    public function login(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();

        $validator = (new Validator($data))
            ->required('email', 'Email')
            ->required('password', 'Password');

        if ($validator->fails()) {
            return Responder::error($response, 'Validation failed.', 422, $validator->errors());
        }

        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $data['email']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            return Responder::error($response, 'Invalid email or password.', 401);
        }

        // Generate JWT token
        $token = JwtHelper::encode([
            'user_id' => (int) $user['user_id'],
            'role'    => $user['role'],
        ]);

        unset($user['password_hash']);

        // Format user_id as int
        $user['user_id'] = (int) $user['user_id'];

        return Responder::json($response, [
            'user'  => $user,
            'token' => $token,
        ]);
    }

    private function findUser(int $userId): array|false
    {
        $stmt = $this->db->prepare('SELECT user_id, name, email, role, title, location, resume_url, bio, skills, created_at FROM users WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch();
        if ($user) {
            $user['user_id'] = (int) $user['user_id'];
        }
        return $user;
    }
}
