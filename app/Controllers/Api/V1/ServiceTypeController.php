<?php

namespace App\Controllers\Api\V1;


use App\Services\serviceTypeService;
use CodeIgniter\RESTful\ResourceController;

class ServiceTypeController extends ResourceController
{
    protected $format = 'json';
    protected $serviceTypeService;

    public function __construct()
    {
        $this->serviceTypeService = new serviceTypeService();
    }

    public function index()
    {
        $perPage = $this->request->getGet('per_page') ?? 10;
        return $this->respond([
            'status' => 'success',
            'message' => 'services type data fetched',
            'data' => $this->serviceTypeService->list($perPage),
            'errors' => null
        ]);
    }

    public function show($id = null)
    {
        $service = $this->serviceTypeService->find($id);
        if (!$service) {
            return $this->failNotFound("service type not founds");
        }
        return $this->respond([
            'status' => 'success',
            'message' => 'service type data fetched',
            'data' => $service,
            'errors' => null
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $insert = $this->serviceTypeService->create($data);
        if (isset($insert['error'])) {
            return $this->failValidationErrors($insert['error']);
        }
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'service type created successfully',
            'data' => $insert,
            'errors' => null
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $service = $this->serviceTypeService->update($id, $data);

        if (isset($service['error'])) {
            if (($service['code'] ?? 500) === 404) {
                return $this->failNotFound($service['error']);
            }

            if (($service['code'] ?? 500) === 409) {
                return $this->failValidationErrors($service['error']);
            }

            if (($service['code'] ?? 500) === 400) {
                return $this->failValidationErrors($service['error']);
            }
        }
        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'service updated successfully',
            'data' => $service,
            'errors' => null
        ]);

    }

    public function delete($id = null)
    {
        $service = $this->serviceTypeService->delete($id);
        if (isset($service['error'])) {
            if (($service['code'] ?? 500) === 404) {
                return $this->failNotFound($service['error']);
            }
        }
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'service type deleted successfully',
            'data' => $id,
            'errors' => null
        ]);
    }

}
