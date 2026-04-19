<?php

namespace App\Controllers\Api\V1;


use App\Services\QueueService;
use CodeIgniter\RESTful\ResourceController;

class QueueController extends ResourceController
{
    protected $format = 'json';
    protected $queueService;

    public function __construct()
    {
        $this->queueService = new QueueService();
    }

    public function index()
    {
        $perPage = $this->request->getGet('per_page') ?? 10;
        return $this->respond([
            'status' => 'success',
            'message' => 'queues data fetched',
            'data' => $this->queueService->list($perPage),
            'errors' => null
        ]);
    }

    public function show($id = null)
    {
        $queue = $this->queueService->find($id);
        if (!$queue) {
            return $this->failNotFound("queue not founds");
        }
        return $this->respond([
            'status' => 'success',
            'message' => 'queue data fetched',
            'data' => $queue,
            'errors' => null
        ]);
    }

    public function create()
    {
        $data = $this->getRequestData();
        $insert = $this->queueService->create($data);
        if (isset($insert['error'])) {
            if (($insert['code'] ?? 500) === 400) {
                return $this->failValidationErrors($insert['error']);
            }

            return $this->failValidationErrors($insert['error']);
        }

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'queue created successfully',
            'data' => $insert,
            'errors' => null
        ]);
    }

    public function update($id = null)
    {
        $data = $this->getRequestData();
        $queue = $this->queueService->update($id, $data);

        if (isset($queue['error'])) {
            if (($queue['code'] ?? 500) === 404) {
                return $this->failNotFound($queue['error']);
            }

            if (($queue['code'] ?? 500) === 400) {
                return $this->failValidationErrors($queue['error']);
            }
        }

        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'queue updated successfully',
            'data' => $queue,
            'errors' => null
        ]);

    }

    public function delete($id = null)
    {
        $queue = $this->queueService->delete($id);
        if (isset($queue['error'])) {
            if (($queue['code'] ?? 500) === 404) {
                return $this->failNotFound($queue['error']);
            }
        }
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'queue deleted successfully',
            'data' => $id,
            'errors' => null
        ]);
    }

    private function getRequestData(): array
    {
        $json = $this->request->getJSON(true);
        if (is_array($json)) {
            return $json;
        }

        $raw = $this->request->getRawInput();
        if (is_array($raw) && $raw !== []) {
            return $raw;
        }

        $post = $this->request->getPost();
        return is_array($post) ? $post : [];
    }
}
