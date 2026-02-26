<?php

namespace App\Services;

use App\Models\ScheduleModel;
use App\Models\ServiceTypeModel;

class ScheduleService
{
    protected $scheduleModel;
    protected $serviceTypeModel;

    public function __construct()
    {
        $this->scheduleModel = new scheduleModel();
        $this->serviceTypeModel = new serviceTypeModel();
    }

    public function list($perPage = 10)
    {
        return $this->scheduleModel->paginate($perPage);
    }

    public function find($id)
    {
        return $this->scheduleModel->find($id);
    }

    public function create($data)
    {
        //butuh ditambah pengecekan service apakah ada atau tidak
        $serviceType = $this->serviceTypeModel->find($data['service_type_id']);
        if (!$serviceType) {
            return [
                'error' => 'service type not found',
                'code' => 404
            ];
        }
        $inserted = $this->scheduleModel->insert($data);
        if ($inserted === false) {
            return ['error' => $this->scheduleModel->errors()];
        }
        return $this->scheduleModel->find($this->scheduleModel->getInsertID());
    }

    public function update($id, $data)
    {
        $schedule = $this->scheduleModel->find($id);

        if (!$schedule) {
            return [
                'error' => 'schedule not found',
                'code' => 404
            ];
        }
        $this->scheduleModel->update($id, $data);
        return $this->scheduleModel->find($id);
    }


    public function delete($id)
    {
        $schedule = $this->scheduleModel->find($id);

        if (!$schedule) {
            return [
                'error' => 'schedule not found',
                'code' => 404
            ];
        }
        return $this->scheduleModel->delete($id);
    }

}
