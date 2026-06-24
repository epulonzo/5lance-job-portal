<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Database;
use App\Helpers\Responder;
use App\Helpers\Validator;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** GET /api/users/{id} — get user profile (JWT authenticated) */
    public function show(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $args['id'];
        $user = $this->findUser($userId);

        if (!$user) {
            return Responder::error($response, 'User not found.', 404);
        }

        return Responder::json($response, $user);
    }

    /** PUT /api/users/{id} — update own profile (JWT Owner only) */
    public function update(Request $request, Response $response, array $args): Response
    {
        $jwtUser = $request->getAttribute('jwt_user');
        $userId = (int) $args['id'];

        if ((int) $jwtUser['user_id'] !== $userId) {
            return Responder::error($response, 'You can only update your own profile.', 403);
        }

        $data = (array) $request->getParsedBody();

        $validator = (new Validator($data))
            ->maxLength('name', 100);

        if ($validator->fails()) {
            return Responder::error($response, 'Validation failed.', 422, $validator->errors());
        }

        // Fetch current user details
        $stmt = $this->db->prepare('SELECT * FROM users WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return Responder::error($response, 'User not found.', 404);
        }

        $name = $data['name'] ?? ($user['name'] ?? null);
        $title = isset($data['title']) ? $data['title'] : ($user['title'] ?? null);
        $location = isset($data['location']) ? $data['location'] : ($user['location'] ?? null);
        $bio = isset($data['bio']) ? $data['bio'] : ($user['bio'] ?? null);
        $skills = isset($data['skills']) ? $data['skills'] : ($user['skills'] ?? null);

        // Handle File Upload
        $uploadedFiles = $request->getUploadedFiles();
        $resumeFile = $uploadedFiles['resume'] ?? null;
        $resumeUrl = $user['resume_url'] ?? null;

        if ($resumeFile && $resumeFile->getError() === UPLOAD_ERR_OK) {
            $filename = $resumeFile->getClientFilename();
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if (strtolower($extension) !== 'pdf') {
                return Responder::error($response, 'Only PDF files are allowed.', 400);
            }
            
            $basename = bin2hex(random_bytes(8)) . '.' . $extension;
            $uploadDir = __DIR__ . '/../../public/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $resumeFile->moveTo($uploadDir . DIRECTORY_SEPARATOR . $basename);
            $resumeUrl = '/uploads/' . $basename;
        }

        $updateStmt = $this->db->prepare(
            'UPDATE users
             SET name = :name, title = :title, location = :location, resume_url = :resume_url, bio = :bio, skills = :skills
             WHERE user_id = :user_id'
        );

        $updateStmt->execute([
            'name'       => $name,
            'title'      => $title,
            'location'   => $location,
            'resume_url' => $resumeUrl,
            'bio'        => $bio,
            'skills'     => $skills,
            'user_id'    => $userId,
        ]);

        $updatedUser = $this->findUser($userId);

        return Responder::json($response, $updatedUser);
    }

    /** GET /api/users — list all user accounts (JWT Admin only) */
    public function index(Request $request, Response $response): Response
    {
        $jwtUser = $request->getAttribute('jwt_user');

        // Only admin can view all users
        if (($jwtUser['role'] ?? '') !== 'admin') {
            return Responder::error($response, 'Only administrators can view all users.', 403);
        }

        $stmt = $this->db->prepare('SELECT user_id, name, email, role, title, location, created_at FROM users ORDER BY created_at DESC');
        $stmt->execute();
        $users = $stmt->fetchAll();

        foreach ($users as &$user) {
            $user['user_id'] = (int) $user['user_id'];
        }

        return Responder::json($response, $users);
    }

    /** DELETE /api/users/{id} — deactivate/delete a user account (JWT Admin only) */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $jwtUser = $request->getAttribute('jwt_user');
        $userId = (int) $args['id'];

        // Only admin can deactivate users
        if (($jwtUser['role'] ?? '') !== 'admin') {
            return Responder::error($response, 'Only administrators can deactivate user accounts.', 403);
        }

        $stmt = $this->db->prepare('SELECT user_id FROM users WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        if (!$stmt->fetch()) {
            return Responder::error($response, 'User not found.', 404);
        }

        $deleteStmt = $this->db->prepare('DELETE FROM users WHERE user_id = :user_id');
        $deleteStmt->execute(['user_id' => $userId]);

        return Responder::json($response, ['message' => 'User account deactivated successfully.']);
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
