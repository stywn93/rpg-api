<?php

namespace App\Controllers\Api\V1;

use App\Models\PatientModel;
use CodeIgniter\RESTful\ResourceController;

class PatientController extends ResourceController
{
    protected $format = 'json';
    protected $patientModel;

    public function __construct()
    {
        $this->patientModel = new PatientModel();
    }

    public function index()
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);

        $name       = trim((string) $this->request->getGet('name') ?? '');
        $genderCode = trim((string) $this->request->getGet('gender_code') ?? '');
        $userId     = trim((string) $this->request->getGet('user_id') ?? '');

        if ($name !== '') {
            $this->patientModel->like('patients.name', $name);
        }
        if ($genderCode !== '') {
            $this->patientModel->where('patients.gender_code', $genderCode);
        }
        if ($userId !== '') {
            $this->patientModel->where('patients.user_id', $userId);
        }

        $patients = $this->patientModel->getPaginatedWithAge($perPage, $page);
        $pager    = $this->patientModel->pager;

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Patients data fetched',
            'data' => $patients,
            'meta' => [
                'total'        => $pager->getTotal(),
                'per_page'     => $pager->getPerPage(),
                'current_page' => $pager->getCurrentPage(),
                'last_page'    => $pager->getPageCount(),
            ],
        ]);
    }

    public function show($id = null)
    {
        $patient = $this->patientModel->findWithAge($id);

        if (!$patient) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Patient not found',
                'data' => $id,
                'errors' => '404',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Patient data fetched',
            'data' => $patient,
            'errors' => null,
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        $inserted = $this->patientModel->insert($data);
        if ($inserted === false) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Error creating patient',
                'data' => $data,
                'errors' => $this->patientModel->errors(),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Patient created',
            'data' => $this->patientModel->findWithAge($this->patientModel->getInsertID()),
            'errors' => '-',
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        $patient = $this->patientModel->find($id);

        if (!$patient) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Patient not found',
                'data' => $id,
                'errors' => '404',
            ]);
        }

        if (isset($data['no_kk'])) {
            if ($this->patientModel->withDeleted()->where('no_kk', $data['no_kk'])->where('id !=', $id)->first()) {
                return $this->response->setJSON([
                    'status' => 'failed',
                    'message' => 'No KK already exists',
                    'data' => $data['no_kk'],
                    'errors' => '409',
                ]);
            }
        }

        $this->patientModel->update($id, $data);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Patient updated',
            'data' => $this->patientModel->findWithAge($id),
            'errors' => '-',
        ]);
    }

    public function delete($id = null)
    {
        $patient = $this->patientModel->find($id);

        if (!$patient) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Patient not found',
                'data' => $id,
                'errors' => '404',
            ]);
        }

        $this->patientModel->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Patient deleted successfully',
            'data' => $id,
            'errors' => '-',
        ]);
    }

    public function showByParent($parentID = null)
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);

        $patients = $this->patientModel->getByParentWithAge((int) $parentID, $perPage, $page);
        $pager    = $this->patientModel->pager;

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Patients data fetched',
            'data' => $patients,
            'meta' => [
                'total'        => $pager->getTotal(),
                'per_page'     => $pager->getPerPage(),
                'current_page' => $pager->getCurrentPage(),
                'last_page'    => $pager->getPageCount(),
            ],
        ]);
    }

    public function listWithParents(?int $parentId = null)
    {
        $patients = $this->patientModel->getAllFromView($parentId);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Patients with parents data fetched',
            'data' => $patients,
            'errors' => null,
        ]);
    }
}
