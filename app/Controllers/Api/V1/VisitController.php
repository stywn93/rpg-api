<?php

namespace App\Controllers\Api\V1;

use App\Models\VisitModel;
use CodeIgniter\RESTful\ResourceController;

class VisitController extends ResourceController
{
    protected $format = 'json';
    protected $visitModel;

    public function __construct()
    {
        $this->visitModel = new VisitModel();
    }

    public function index()
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);

        $patientId = trim((string) $this->request->getGet('patient_id') ?? '');
        $visitDate = trim((string) $this->request->getGet('visit_date') ?? '');

        if ($patientId !== '') {
            $this->visitModel->where('patient_id', $patientId);
        }
        if ($visitDate !== '') {
            $this->visitModel->where('visit_date', $visitDate);
        }

        $visits = $this->visitModel->getPaginatedWithPatient($perPage, $page);
        $pager  = $this->visitModel->pager;

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Visits data fetched',
            'data' => $visits,
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
        $visit = $this->visitModel->findWithPatient($id);

        if (!$visit) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Visit not found',
                'data' => $id,
                'errors' => '404',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Visit data fetched',
            'data' => $visit,
            'errors' => null,
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        $inserted = $this->visitModel->insert($data);
        if ($inserted === false) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Error creating visit',
                'data' => $data,
                'errors' => $this->visitModel->errors(),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Visit created',
            'data' => $this->visitModel->findWithPatient($this->visitModel->getInsertID()),
            'errors' => '-',
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        $visit = $this->visitModel->find($id);

        if (!$visit) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Visit not found',
                'data' => $id,
                'errors' => '404',
            ]);
        }

        $this->visitModel->update($id, $data);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Visit updated',
            'data' => $this->visitModel->findWithPatient($id),
            'errors' => '-',
        ]);
    }

    public function delete($id = null)
    {
        $visit = $this->visitModel->find($id);

        if (!$visit) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Visit not found',
                'data' => $id,
                'errors' => '404',
            ]);
        }

        $this->visitModel->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Visit deleted successfully',
            'data' => $id,
            'errors' => '-',
        ]);
    }
}
