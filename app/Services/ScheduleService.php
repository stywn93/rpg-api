<?php

namespace App\Services;

use App\Models\ScheduleModel;

class ScheduleService
{
    protected $scheduleModel;

    public function __construct()
    {
        $this->scheduleModel = new scheduleModel();
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
        if ($this->scheduleModel->withDeleted()->where('hari', $data['hari'])->first()) {
            return [
                'error' => 'Those day already exists.',
                'code' => 409
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
