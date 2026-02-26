<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Services\patientService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class PatientController extends ResourceController
{
    protected $format = 'json';
    protected $patientService;

    public function __construct()
    {
        $this->patientService = new patientService();
    }

    public function index()
    {
        $perPage = $this->request->getGet('per_page') ?? 10;
        return $this->respond([
            'status' => 'success',
            'message' => 'patients data fetched',
            'data' => $this->patientService->list($perPage),
            'errors' => null
        ]);
    }

    public function show($id = null)
    {
        $patient = $this->patientService->find($id);
        if (!$patient) {
            return $this->failNotFound("patient not founds");
        }
        return $this->respond([
            'status' => 'success',
            'message' => 'patient data fetched',
            'data' => $patient,
            'errors' => null
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $insert = $this->patientService->create($data);
        if (isset($insert['error'])) {
            return $this->failValidationErrors($insert['error']);
        }
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'patient created successfully',
            'data' => $insert,
            'errors' => null
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $patient = $this->patientService->update($id, $data);

        if (isset($patient['error'])) {
            if (($patient['code'] ?? 500) === 404) {
                return $this->failNotFound($patient['error']);
            }

            if (($patient['code'] ?? 500) === 409) {
                return $this->failValidationErrors($patient['error']);
            }

            if (($patient['code'] ?? 500) === 400) {
                return $this->failValidationErrors($patient['error']);
            }
        }
        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'patient updated successfully',
            'data' => $patient,
            'errors' => null
        ]);

    }

    public function delete($id = null)
    {
        $patient = $this->patientService->delete($id);
        if (isset($patient['error'])) {
            if (($patient['code'] ?? 500) === 404) {
                return $this->failNotFound($patient['error']);
            }
        }
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'patient deleted successfully',
            'data' => $id,
            'errors' => null
        ]);
    }

    public function showByParent($parentID = null)
    {
        $patients = $this->patientService->getByParent($parentID);
        if (!$patients) {
            return $this->failNotFound("patient not found");
        }
        return $this->respond([
            'status' => 'success',
            'message' => 'patient data fetched',
            'data' => $patients,
            'errors' => null
        ]);
    }



}
