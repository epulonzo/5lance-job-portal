<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Database;
use App\Helpers\Responder;
use App\Helpers\Validator;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class JobController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** GET /api/jobs — list all open job postings, with optional filters */
    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $sql = 'SELECT j.*, u.name AS client_name
                FROM jobs j
                INNER JOIN users u ON u.user_id = j.client_id
                WHERE 1 = 1';
        $bindings = [];

        if (!empty($params['category'])) {
            $sql .= ' AND j.category = :category';
            $bindings['category'] = $params['category'];
        }

        if (!empty($params['status']) && $params['status'] !== 'all') {
            $sql .= ' AND j.status = :status';
            $bindings['status'] = $params['status'];
        } elseif (empty($params['status'])) {
            $sql .= " AND j.status = 'open'";
        }

        if (isset($params['min_budget']) && is_numeric($params['min_budget'])) {
            $sql .= ' AND j.budget >= :min_budget';
            $bindings['min_budget'] = $params['min_budget'];
        }

        if (isset($params['max_budget']) && is_numeric($params['max_budget'])) {
            $sql .= ' AND j.budget <= :max_budget';
            $bindings['max_budget'] = $params['max_budget'];
        }

        if (!empty($params['search'])) {
            $sql .= ' AND (j.title LIKE :search OR j.description LIKE :search)';
            $bindings['search'] = '%' . $params['search'] . '%';
        }

        $sql .= ' ORDER BY j.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return Responder::json($response, $stmt->fetchAll());
    }

    /** GET /api/jobs/{id} — get single job details */
    public function show(Request $request, Response $response, array $args): Response
    {
        $job = $this->findJob((int) $args['id']);

        if (!$job) {
            return Responder::error($response, 'Job not found.', 404);
        }

        return Responder::json($response, $job);
    }

    /** POST /api/jobs — create a new job posting (JWT Client only) */
    public function create(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('jwt_user');
        $data = (array) $request->getParsedBody();

        $validator = (new Validator($data))
            ->required('title', 'Title')->maxLength('title', 200)
            ->required('description', 'Description')
            ->required('category', 'Category')->maxLength('category', 100)
            ->numeric('budget')->min('budget', 0)
            ->date('deadline');

        if ($validator->fails()) {
            return Responder::error($response, 'Validation failed.', 422, $validator->errors());
        }

        $stmt = $this->db->prepare(
            'INSERT INTO jobs (client_id, title, description, category, budget, deadline, status)
             VALUES (:client_id, :title, :description, :category, :budget, :deadline, :status)'
        );

        $stmt->execute([
            'client_id'   => $user['user_id'],
            'title'       => $data['title'],
            'description' => $data['description'],
            'category'    => $data['category'],
            'budget'      => (!isset($data['budget']) || $data['budget'] === '') ? null : $data['budget'],
            'deadline'    => (!isset($data['deadline']) || $data['deadline'] === '') ? null : $data['deadline'],
            'status'      => $data['status'] ?? 'open',
        ]);

        $job = $this->findJob((int) $this->db->lastInsertId());

        return Responder::json($response, $job, 201);
    }

    /** PUT /api/jobs/{id} — update an existing job posting (JWT Client, owner only) */
    public function update(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('jwt_user');
        $jobId = (int) $args['id'];

        $job = $this->findJob($jobId);
        if (!$job) {
            return Responder::error($response, 'Job not found.', 404);
        }

        if ((int) $job['client_id'] !== (int) $user['user_id']) {
            return Responder::error($response, 'You can only edit your own job postings.', 403);
        }

        $data = (array) $request->getParsedBody();

        $validator = (new Validator($data))
            ->required('title', 'Title')->maxLength('title', 200)
            ->required('description', 'Description')
            ->required('category', 'Category')->maxLength('category', 100)
            ->numeric('budget')->min('budget', 0)
            ->date('deadline')
            ->in('status', ['open', 'closed', 'awarded']);

        if ($validator->fails()) {
            return Responder::error($response, 'Validation failed.', 422, $validator->errors());
        }

        $stmt = $this->db->prepare(
            'UPDATE jobs
             SET title = :title, description = :description, category = :category,
                 budget = :budget, deadline = :deadline, status = :status
             WHERE job_id = :job_id'
        );

        $stmt->execute([
            'title'       => $data['title'],
            'description' => $data['description'],
            'category'    => $data['category'],
            'budget'      => (!isset($data['budget']) || $data['budget'] === '') ? null : $data['budget'],
            'deadline'    => (!isset($data['deadline']) || $data['deadline'] === '') ? null : $data['deadline'],
            'status'      => $data['status'] ?? $job['status'],
            'job_id'      => $jobId,
        ]);

        return Responder::json($response, $this->findJob($jobId));
    }

    /** DELETE /api/jobs/{id} — delete a job posting (JWT Client owner or Admin) */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('jwt_user');
        $jobId = (int) $args['id'];

        $job = $this->findJob($jobId);
        if (!$job) {
            return Responder::error($response, 'Job not found.', 404);
        }

        $isOwner = (int) $job['client_id'] === (int) $user['user_id'];
        $isAdmin = ($user['role'] ?? null) === 'admin';

        if (!$isOwner && !$isAdmin) {
            return Responder::error($response, 'You do not have permission to delete this job.', 403);
        }

        $stmt = $this->db->prepare('DELETE FROM jobs WHERE job_id = :job_id');
        $stmt->execute(['job_id' => $jobId]);

        return Responder::json($response, ['message' => 'Job deleted successfully.']);
    }

    private function findJob(int $jobId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT j.*, u.name AS client_name
             FROM jobs j
             INNER JOIN users u ON u.user_id = j.client_id
             WHERE j.job_id = :job_id'
        );
        $stmt->execute(['job_id' => $jobId]);

        return $stmt->fetch();
    }
}
