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
        if ($this->scheduleModel->withDeleted()->where('nik', $data['nik'])->first()) {
            return [
                'error' => 'NIK already used',
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
        if(isset($data['nik'])){
            if ($this->scheduleModel->where('nik', $data['nik'])->where('id !=', $id)->first()) {
                return [
                    'error' => 'NIK already used',
                    'code' => 409
                ];
            }
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

    public function getByParent($parentID){
        return $this->scheduleModel->where('parent_id', $parentID)->paginate(10);
    }

}
