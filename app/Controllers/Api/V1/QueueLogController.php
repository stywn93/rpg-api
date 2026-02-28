<?php

namespace App\Controllers\Api\V1;


use App\Services\queueLogService;
use CodeIgniter\RESTful\ResourceController;

class QueueLogController extends ResourceController
{
    protected $format = 'json';
    protected $queueLogService;

    public function __construct()
    {
        $this->queueLogService = new QueueLogService();
    }

    public function index()
    {
        $perPage = $this->request->getGet('per_page') ?? 10;
        return $this->respond([
            'status' => 'success',
            'message' => 'queue logs data fetched',
            'data' => $this->queueLogService->list($perPage),
            'errors' => null
        ]);
    }

    public function show($id = null)
    {
        $queue = $this->queueLogService->find($id);
        if (!$queue) {
            return $this->failNotFound("queue log not founds");
        }
        return $this->respond([
            'status' => 'success',
            'message' => 'queue logs data fetched',
            'data' => $queue,
            'errors' => null
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $insert = $this->queueLogService->create($data);
        if (isset($insert['error'])) {
            return $this->failValidationErrors($insert['error']);
        }
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'queue log created successfully',
            'data' => $insert,
            'errors' => null
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $queueLog = $this->queueLogService->update($id, $data);

        if (isset($queueLog['error'])) {
            if (($queueLog['code'] ?? 500) === 404) {
                return $this->failNotFound($queueLog['error']);
            }

            if (($queueLog['code'] ?? 500) === 409) {
                return $this->failValidationErrors($queueLog['error']);
            }

            if (($queueLog['code'] ?? 500) === 400) {
                return $this->failValidationErrors($queueLog['error']);
            }
        }
        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'queue updated successfully',
            'data' => $queueLog,
            'errors' => null
        ]);

    }

    public function delete($id = null)
    {
        $queue = $this->queueLogService->delete($id);
        if (isset($queue['error'])) {
            if (($queue['code'] ?? 500) === 404) {
                return $this->failNotFound($queueLog['error']);
            }
        }
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'queue log deleted successfully',
            'data' => $id,
            'errors' => null
        ]);
    }

}
