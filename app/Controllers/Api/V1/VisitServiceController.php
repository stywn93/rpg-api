<?php

namespace App\Controllers\Api\V1;

use App\Models\VisitModel;
use App\Models\MedicalServiceModel;
use App\Models\VisitServiceModel;
use CodeIgniter\RESTful\ResourceController;

class VisitServiceController extends ResourceController
{
    protected $format = 'json';
    protected $visitServiceModel;
    protected $visitModel;
    protected $serviceModel;

    public function __construct()
    {
        $this->visitServiceModel = new VisitServiceModel();
        $this->visitModel = new VisitModel();
        $this->serviceModel = new MedicalServiceModel();
    }

    private function getVisitServiceFilters(): array
    {
        return array_filter([
            'visit_id'     => trim((string) ($this->request->getGet('visit_id') ?? '')),
            'service_id'   => trim((string) ($this->request->getGet('service_id') ?? '')),
            'performed_by' => trim((string) ($this->request->getGet('performed_by') ?? '')),
            'visit_date'   => trim((string) ($this->request->getGet('visit_date') ?? '')),
            'visit_status' => trim((string) ($this->request->getGet('visit_status') ?? '')),
            'patient_name' => trim((string) ($this->request->getGet('patient_name') ?? '')),
            'parent_id'    => trim((string) ($this->request->getGet('parent_id') ?? '')),
            'gender'       => trim((string) ($this->request->getGet('gender') ?? '')),
            'patient_id'   => trim((string) ($this->request->getGet('patient_id') ?? '')),
        ], fn ($value) => $value !== '');
    }

    public function index()
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);

        $filters = $this->getVisitServiceFilters();

        $visitServices = $this->visitServiceModel->getVisitServicesWithPatient($perPage, $page, $filters);
        $pager         = $this->visitServiceModel->pager;

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Visit services data fetched',
            'data' => $visitServices,
            'meta' => [
                'total'        => $pager->getTotal(),
                'per_page'     => $pager->getPerPage(),
                'current_page' => $pager->getCurrentPage(),
                'last_page'    => $pager->getPageCount(),
            ],
        ]);
    }

    public function rows()
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);

        $filters     = $this->getVisitServiceFilters();
        $serviceRows = $this->visitServiceModel->getVisitServiceRows($perPage, $page, $filters);
        $pager       = $this->visitServiceModel->pager;

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Visit service rows data fetched',
            'data'    => $serviceRows,
            'meta'    => [
                'total'        => $pager->getTotal(),
                'per_page'     => $pager->getPerPage(),
                'current_page' => $pager->getCurrentPage(),
                'last_page'    => $pager->getPageCount(),
            ],
        ]);
    }

    public function show($id = null)
    {
        $visitService = $this->visitServiceModel->findWithDetails($id);

        if (!$visitService) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Visit service not found',
                'data' => $id,
                'errors' => '404',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Visit service data fetched',
            'data' => $visitService,
            'errors' => null,
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        $inserted = $this->visitServiceModel->insert($data);
        if ($inserted === false) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Error creating visit service',
                'data' => $data,
                'errors' => $this->visitServiceModel->errors(),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Visit service created',
            'data' => $this->visitServiceModel->findWithDetails($this->visitServiceModel->getInsertID()),
            'errors' => '-',
        ]);
    }

    public function update($id = null)
    {
        $existing = $this->visitServiceModel->find($id);

        if (!$existing) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Visit service not found',
                'data' => $id,
                'errors' => '404',
            ]);
        }

        $data = $this->request->getJSON(true);
        if (empty($data)) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'No data provided',
                'data' => $id,
                'errors' => '400',
            ]);
        }

        // Merge data existing agar rule "required" di model lolos untuk PATCH parsial
        $merged = array_merge($existing, $data);

        if ($this->visitServiceModel->update($id, $merged) === false) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Error updating visit service',
                'data' => $data,
                'errors' => $this->visitServiceModel->errors(),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Visit service updated',
            'data' => $this->visitServiceModel->findWithDetails((int) $id),
            'errors' => '-',
        ]);
    }

    public function delete($id = null)
    {
        $visitService = $this->visitServiceModel->find($id);

        if (!$visitService) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Visit service not found',
                'data' => $id,
                'errors' => '404',
            ]);
        }

        $this->visitServiceModel->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Visit service deleted successfully',
            'data' => $id,
            'errors' => '-',
        ]);
    }


    // app/Controllers/Api/VisitController.php
    public function updateServices($visitId = null)
    {
        $visit = $this->visitModel->find($visitId);
        if (!$visit) {
            return $this->failNotFound('Visit not found');
        }

        $rules = [
            'service_id'   => 'required|is_array',
            'service_id.*' => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $serviceIds = $this->request->getJSON(true)['service_id'];
        $serviceIds = array_unique($serviceIds);

        // Pastikan semua service_id valid
        $existingCount = $this->serviceModel->whereIn('service_id', $serviceIds)->countAllResults();
        if ($existingCount !== count($serviceIds)) {
            return $this->failValidationErrors(['service_id' => 'Ada service_id yang tidak valid']);
        }

        $success = $this->visitServiceModel->syncVisitServices($visitId, $serviceIds);

        if (!$success) {
            return $this->failServerError('Failed to sync visit services');
        }

        $result = $this->visitServiceModel->getServicesWithDetail($visitId);

        return $this->respond([
            'status'  => 200,
            'message' => 'Visit services updated successfully',
            'data'  => $visit,
            'service_ids' => $serviceIds,
            'existing_count' => $existingCount,
            'result' => $result,
        ]);
    }


}