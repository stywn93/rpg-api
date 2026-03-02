<?php

namespace App\Services;

use App\Models\GrowthRecordModel;

class GrowthRecordService
{
    protected $growthRecordModel;

    public function __construct()
    {
        $this->growthRecordModel = new growthRecordModel();
    }

    public function list($perPage = 10)
    {
        return $this->growthRecordModel->paginate($perPage);
    }

    public function find($id)
    {
        return $this->growthRecordModel->find($id);
    }

    public function create($data)
    {
        $inserted = $this->growthRecordModel->insert($data);
        if ($inserted === false) {
            return ['error' => $this->growthRecordModel->errors()];
        }
        return $this->growthRecordModel->find($this->growthRecordModel->getInsertID());
    }

    public function update($id, $data)
    {
        $growthRecord = $this->growthRecordModel->find($id);

        if (!$growthRecord) {
            return [
                'error' => 'growthRecord not found',
                'code' => 404
            ];
        }

        $this->growthRecordModel->update($id, $data);
        return $this->growthRecordModel->find($id);
    }


    public function delete($id)
    {
        $growthRecord = $this->growthRecordModel->find($id);

        if (!$growthRecord) {
            return [
                'error' => 'growth record not found',
                'code' => 404
            ];
        }
        return $this->growthRecordModel->delete($id);
    }

}
