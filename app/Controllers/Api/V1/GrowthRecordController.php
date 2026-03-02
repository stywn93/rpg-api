<?php

namespace App\Controllers\Api\V1;

use App\Services\growthRecordService;
use CodeIgniter\RESTful\ResourceController;

class GrowthRecordController extends ResourceController
{
    protected $format = 'json';

    public function __construct()
    {
        $this->growthRecordService = new growthRecordService();
    }

    public function index()
    {
        $perPage = $this->request->getGet('per_page') ?? 10;
        return $this->respond([
            'status' => 'success',
            'message' => 'growth records data fetched',
            'data' => $this->growthRecordService->list($perPage),
            'errors' => null
        ]);
    }

    public function show($id = null)
    {
        $growthRecord = $this->growthRecordService->find($id);
        if (!$growthRecord) {
            return $this->failNotFound("growth record not founds");
        }
        return $this->respond([
            'status' => 'success',
            'message' => 'growth records data fetched',
            'data' => $growthRecord,
            'errors' => null
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $insert = $this->growthRecordService->create($data);
        if (isset($insert['error'])) {
            return $this->failValidationErrors($insert['error']);
        }
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'growth records created successfully',
            'data' => $insert,
            'errors' => null
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $growthRecord = $this->growthRecordService->update($id, $data);

        if (isset($growthRecord['error'])) {
            if (($growthRecord['code'] ?? 500) === 404) {
                return $this->failNotFound($growthRecord['error']);
            }

            if (($growthRecord['code'] ?? 500) === 409) {
                return $this->failValidationErrors($growthRecord['error']);
            }

            if (($growthRecord['code'] ?? 500) === 400) {
                return $this->failValidationErrors($growthRecord['error']);
            }
        }
        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'growth record updated successfully',
            'data' => $growthRecord,
            'errors' => null
        ]);

    }

    public function delete($id = null)
    {
        $growthRecord = $this->growthRecordService->delete($id);
        if (isset($growthRecord['error'])) {
            if (($growthRecord['code'] ?? 500) === 404) {
                return $this->failNotFound($growthRecord['error']);
            }
        }
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'growth record deleted successfully',
            'data' => $id,
            'errors' => null
        ]);
    }

}
