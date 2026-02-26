<?php

namespace App\Controllers\Api\V1;


use App\Services\queueService;
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
        $data = $this->request->getJSON(true);
        $insert = $this->queueService->create($data);
        if (isset($insert['error'])) {
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
        $data = $this->request->getJSON(true);
        $queue = $this->queueService->update($id, $data);

        if (isset($queue['error'])) {
            if (($queue['code'] ?? 500) === 404) {
                return $this->failNotFound($queue['error']);
            }

            if (($queue['code'] ?? 500) === 409) {
                return $this->failValidationErrors($queue['error']);
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

}
