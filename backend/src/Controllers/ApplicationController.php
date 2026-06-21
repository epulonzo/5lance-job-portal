<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Database;
use App\Helpers\Responder;
use App\Helpers\Validator;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ApplicationController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** GET /api/applications — list all relevant applications */
    public function index(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('jwt_user');
        $role = $user['role'] ?? '';

        if ($role === 'admin') {
            $stmt = $this->db->prepare(
                'SELECT a.*, j.title AS job_title, j.client_id, u.name AS freelancer_name, u.email AS freelancer_email, u.resume_url AS freelancer_resume_url, c.name AS client_name
                 FROM applications a
                 INNER JOIN jobs j ON j.job_id = a.job_id
                 INNER JOIN users u ON u.user_id = a.freelancer_id
                 INNER JOIN users c ON c.user_id = j.client_id
                 ORDER BY a.applied_at DESC'
            );
            $stmt->execute();
        } elseif ($role === 'freelancer') {
            $stmt = $this->db->prepare(
                'SELECT a.*, j.title AS job_title, j.client_id, u.name AS freelancer_name, u.email AS freelancer_email, u.resume_url AS freelancer_resume_url, c.name AS client_name
                 FROM applications a
                 INNER JOIN jobs j ON j.job_id = a.job_id
                 INNER JOIN users u ON u.user_id = a.freelancer_id
                 INNER JOIN users c ON c.user_id = j.client_id
                 WHERE a.freelancer_id = :freelancer_id
                 ORDER BY a.applied_at DESC'
            );
            $stmt->execute(['freelancer_id' => $user['user_id']]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT a.*, j.title AS job_title, j.client_id, u.name AS freelancer_name, u.email AS freelancer_email, u.resume_url AS freelancer_resume_url, c.name AS client_name
                 FROM applications a
                 INNER JOIN jobs j ON j.job_id = a.job_id
                 INNER JOIN users u ON u.user_id = a.freelancer_id
                 INNER JOIN users c ON c.user_id = j.client_id
                 WHERE j.client_id = :client_id
                 ORDER BY a.applied_at DESC'
            );
            $stmt->execute(['client_id' => $user['user_id']]);
        }

        return Responder::json($response, $stmt->fetchAll());
    }

    /** GET /api/jobs/{id}/applications — list applications for a job (JWT Client, owner only) */
    public function indexForJob(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('jwt_user');
        $jobId = (int) $args['id'];

        $job = $this->findJobOwner($jobId);
        if (!$job) {
            return Responder::error($response, 'Job not found.', 404);
        }

        if ((int) $job['client_id'] !== (int) $user['user_id']) {
            return Responder::error($response, 'You can only view applications for your own job postings.', 403);
        }

        $stmt = $this->db->prepare(
            'SELECT a.*, u.name AS freelancer_name, u.email AS freelancer_email, u.skills AS freelancer_skills, u.resume_url AS freelancer_resume_url
             FROM applications a
             INNER JOIN users u ON u.user_id = a.freelancer_id
             WHERE a.job_id = :job_id
             ORDER BY a.applied_at DESC'
        );
        $stmt->execute(['job_id' => $jobId]);

        return Responder::json($response, $stmt->fetchAll());
    }

    /** POST /api/jobs/{id}/applications — submit an application (JWT Freelancer) */
    public function create(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('jwt_user');
        $jobId = (int) $args['id'];

        $job = $this->findJobOwner($jobId);
        if (!$job) {
            return Responder::error($response, 'Job not found.', 404);
        }

        if ($job['status'] !== 'open') {
            return Responder::error($response, 'This job is no longer accepting applications.', 409);
        }

        if ((int) $job['client_id'] === (int) $user['user_id']) {
            return Responder::error($response, 'You cannot apply to your own job posting.', 403);
        }

        $data = (array) $request->getParsedBody();

        $validator = (new Validator($data))
            ->required('cover_letter', 'Cover letter')
            ->numeric('proposed_rate')->min('proposed_rate', 0);

        if ($validator->fails()) {
            return Responder::error($response, 'Validation failed.', 422, $validator->errors());
        }

        $existing = $this->db->prepare(
            'SELECT app_id FROM applications WHERE job_id = :job_id AND freelancer_id = :freelancer_id'
        );
        $existing->execute(['job_id' => $jobId, 'freelancer_id' => $user['user_id']]);
        if ($existing->fetch()) {
            return Responder::error($response, 'You have already applied for this job.', 409);
        }

        $stmt = $this->db->prepare(
            'INSERT INTO applications (job_id, freelancer_id, cover_letter, proposed_rate, status)
             VALUES (:job_id, :freelancer_id, :cover_letter, :proposed_rate, :status)'
        );
        $stmt->execute([
            'job_id'        => $jobId,
            'freelancer_id' => $user['user_id'],
            'cover_letter'  => $data['cover_letter'],
            'proposed_rate' => (!isset($data['proposed_rate']) || $data['proposed_rate'] === '') ? null : $data['proposed_rate'],
            'status'        => 'pending',
        ]);

        $application = $this->findApplication((int) $this->db->lastInsertId());

        return Responder::json($response, $application, 201);
    }

    /** PUT /api/applications/{id} — update application status (JWT Client, job owner only) */
    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('jwt_user');
        $appId = (int) $args['id'];

        $application = $this->findApplication($appId);
        if (!$application) {
            return Responder::error($response, 'Application not found.', 404);
        }

        if ((int) $application['client_id'] !== (int) $user['user_id']) {
            return Responder::error($response, 'You can only manage applications for your own job postings.', 403);
        }

        $data = (array) $request->getParsedBody();

        $validator = (new Validator($data))
            ->required('status', 'Status')
            ->in('status', ['pending', 'accepted', 'rejected']);

        if ($validator->fails()) {
            return Responder::error($response, 'Validation failed.', 422, $validator->errors());
        }

        $stmt = $this->db->prepare('UPDATE applications SET status = :status WHERE app_id = :app_id');
        $stmt->execute(['status' => $data['status'], 'app_id' => $appId]);

        return Responder::json($response, $this->findApplication($appId));
    }

    /** DELETE /api/applications/{id} — withdraw an application (JWT Freelancer, owner only) */
    public function withdraw(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('jwt_user');
        $appId = (int) $args['id'];

        $application = $this->findApplication($appId);
        if (!$application) {
            return Responder::error($response, 'Application not found.', 404);
        }

        if ((int) $application['freelancer_id'] !== (int) $user['user_id']) {
            return Responder::error($response, 'You can only withdraw your own applications.', 403);
        }

        $stmt = $this->db->prepare('DELETE FROM applications WHERE app_id = :app_id');
        $stmt->execute(['app_id' => $appId]);

        return Responder::json($response, ['message' => 'Application withdrawn successfully.']);
    }

    private function findJobOwner(int $jobId): array|false
    {
        $stmt = $this->db->prepare('SELECT job_id, client_id, status FROM jobs WHERE job_id = :job_id');
        $stmt->execute(['job_id' => $jobId]);

        return $stmt->fetch();
    }

    private function findApplication(int $appId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, j.title AS job_title, j.client_id
             FROM applications a
             INNER JOIN jobs j ON j.job_id = a.job_id
             WHERE a.app_id = :app_id'
        );
        $stmt->execute(['app_id' => $appId]);

        return $stmt->fetch();
    }
}
