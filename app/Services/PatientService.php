<?php

namespace App\Services;

use App\Models\PatientModel;

class PatientService
{
    protected $patientModel;

    public function __construct()
    {
        $this->patientModel = new PatientModel();
    }

    public function list($perPage = 10)
    {
        return $this->patientModel->paginate($perPage);
    }

    public function find($id)
    {
        return $this->patientModel->find($id);
    }

    public function create($data)
    {
        $inserted = $this->patientModel->insert($data);
        if ($inserted === false) {
            return ['error' => $this->patientModel->errors()];
        }
        return $this->patientModel->find($this->patientModel->getInsertID());
    }

    public function update($id, $data)
    {
        $patient = $this->patientModel->find($id);

        if (!$patient) {
            return [
                'error' => 'patient not found',
                'code' => 404
            ];
        }
        if(isset($data['nik'])){
            if ($this->patientModel->where('nik', $data['nik'])->where('id !=', $id)->first()) {
                return [
                    'error' => 'NIK already used',
                    'code' => 409
                ];
            }
        }

        $this->patientModel->update($id, $data);
        return $this->patientModel->find($id);
    }


    public function delete($id)
    {
        $patient = $this->patientModel->find($id);

        if (!$patient) {
            return [
                'error' => 'patient not found',
                'code' => 404
            ];
        }
        return $this->patientModel->delete($id);
    }

    public function getByParent($parentID){
        return $this->patientModel->where('parent_id', $parentID)->findAll();
//        return $patients;
    }

}
