<?php

namespace App\Controllers\Api\V1;

use App\Models\VisitServiceModel;
use CodeIgniter\RESTful\ResourceController;

class VisitServiceController extends ResourceController
{
    protected $format = 'json';
    protected $visitServiceModel;

    public function __construct()
    {
        $this->visitServiceModel = new VisitServiceModel();
    }

    public function index()
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);

        $visitId     = trim((string) $this->request->getGet('visit_id') ?? '');
        $serviceId   = trim((string) $this->request->getGet('service_id') ?? '');
        $performedBy = trim((string) $this->request->getGet('performed_by') ?? '');

        if ($visitId !== '') {
            $this->visitServiceModel->where('visit_services.visit_id', $visitId);
        }
        if ($serviceId !== '') {
            $this->visitServiceModel->where('visit_services.service_id', $serviceId);
        }
        if ($performedBy !== '') {
            $this->visitServiceModel->where('visit_services.performed_by', $performedBy);
        }

        $visitServices = $this->visitServiceModel->getPaginatedWithDetails($perPage, $page);
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
        $data = $this->request->getJSON(true);

        $visitService = $this->visitServiceModel->find($id);

        if (!$visitService) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Visit service not found',
                'data' => $id,
                'errors' => '404',
            ]);
        }

        $this->visitServiceModel->update($id, $data);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Visit service updated',
            'data' => $this->visitServiceModel->findWithDetails($id),
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
}
