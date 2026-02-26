<?php

namespace App\Services;

use App\Models\ServiceTypeModel;

class ServiceTypeService
{
    protected $serviceTypeModel;

    public function __construct()
    {
        $this->serviceTypeModel = new serviceTypeModel();
    }

    public function list($perPage = 10)
    {
        return $this->serviceTypeModel->paginate($perPage);
    }

    public function find($id)
    {
        return $this->serviceTypeModel->find($id);
    }

    public function create($data)
    {
        if ($this->serviceTypeModel->withDeleted()->where('nama_layanan', $data['nama_layanan'])->first()) {
            return [
                'error' => 'Those service already exists.',
                'code' => 409
            ];
        }
        $inserted = $this->serviceTypeModel->insert($data);
        if ($inserted === false) {
            return ['error' => $this->serviceTypeModel->errors()];
        }
        return $this->serviceTypeModel->find($this->serviceTypeModel->getInsertID());
    }

    public function update($id, $data)
    {
        $schedule = $this->serviceTypeModel->find($id);

        if (!$schedule) {
            return [
                'error' => 'schedule not found',
                'code' => 404
            ];
        }
        $this->serviceTypeModel->update($id, $data);
        return $this->serviceTypeModel->find($id);
    }


    public function delete($id)
    {
        $schedule = $this->serviceTypeModel->find($id);

        if (!$schedule) {
            return [
                'error' => 'schedule not found',
                'code' => 404
            ];
        }
        return $this->serviceTypeModel->delete($id);
    }

}
