<?php

namespace App\Controllers\Api\V1;

use App\Models\MedicalServiceModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Database\Exceptions\DatabaseException;

class MedicalServiceController extends ResourceController
{
    protected $format = 'json';
    protected $medicalServiceModel;

    public function __construct()
    {
        $this->medicalServiceModel = new MedicalServiceModel();
    }

    public function index()
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);

        $serviceId   = trim((string) $this->request->getGet('service_id') ?? '');
        $serviceName = trim((string) $this->request->getGet('service_name') ?? '');

        if ($serviceId !== '') {
            $this->medicalServiceModel->where('service_id', $serviceId);
        }
        if ($serviceName !== '') {
            $this->medicalServiceModel->like('service_name', $serviceName);
        }

        $services = $this->medicalServiceModel->paginate($perPage, 'default', $page);
        $pager    = $this->medicalServiceModel->pager;

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Medical services data fetched',
            'data' => $services,
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
        $service = $this->medicalServiceModel->find($id);

        if (!$service) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Medical service not found',
                'data' => $id,
                'errors' => '404',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Medical service data fetched',
            'data' => $service,
            'errors' => null,
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (isset($data['service_name'])) {
            if ($this->medicalServiceModel->where('service_name', $data['service_name'])->first()) {
                return $this->response->setJSON([
                    'status' => 'failed',
                    'message' => 'Service name already exists',
                    'data' => $data['service_name'],
                    'errors' => '409',
                ]);
            }
        }

        $inserted = $this->medicalServiceModel->insert($data);
        if ($inserted === false) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Error creating medical service',
                'data' => $data,
                'errors' => $this->medicalServiceModel->errors(),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Medical service created',
            'data' => $this->medicalServiceModel->find($this->medicalServiceModel->getInsertID()),
            'errors' => '-',
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        $service = $this->medicalServiceModel->find($id);

        if (!$service) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Medical service not found',
                'data' => $id,
                'errors' => '404',
            ]);
        }

        if (isset($data['service_name'])) {
            if ($this->medicalServiceModel->where('service_name', $data['service_name'])->where('service_id !=', $id)->first()) {
                return $this->response->setJSON([
                    'status' => 'failed',
                    'message' => 'Service name already exists',
                    'data' => $data['service_name'],
                    'errors' => '409',
                ]);
            }
        }

        $this->medicalServiceModel->update($id, $data);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Medical service updated',
            'data' => $this->medicalServiceModel->find($id),
            'errors' => '-',
        ]);
    }

    public function delete($id = null)
    {
        $service = $this->medicalServiceModel->find($id);

        if (!$service) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Medical service not found',
                'data' => $id,
                'errors' => '404',
            ]);
        }

        try {
            $this->medicalServiceModel->delete($id);
        } catch (DatabaseException $e) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Medical service is in use and cannot be deleted',
                'data' => $id,
                'errors' => '409',
            ]);
        }

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Medical service deleted successfully',
            'data' => $id,
            'errors' => '-',
        ]);
    }
}
