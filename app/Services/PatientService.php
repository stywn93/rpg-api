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
        if ($this->patientModel->where('email', $data['email'])->where('id !=', $id)->first()) {
            return [
                'error' => 'email already exists',
                'code' => 409
            ];
        }
        if (isset($data['status'])) {
            return [
                'error' => 'wrong API to activate or suspend',
                'code' => 400
            ];
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

    public function activate($id)
    {
        $patient = $this->patientModel->find($id);
        if (!$patient) {
            return [
                'error' => 'patient not found',
                'code' => 404,
            ];
        }

        $updated = $this->patientModel->update($id, ['status' => 'active']);
        if ($updated === false) {
            return [
                'error' => $this->patientModel->errors(),
                'code' => 422,
            ];
        }

        return [
            'success' => true,
            'data' => $this->patientModel->find($id),
        ];
    }

    public function suspend($id)
    {
        $patient = $this->patientModel->find($id);
        if (!$patient) {
            return [
                'error' => 'patient not found',
                'code' => 404,
            ];
        }

        $updated = $this->patientModel->update($id, ['status' => 'suspended']);

        if ($updated === false) {
            return [
                'error' => $this->patientModel->errors(),
                'code' => 422,
            ];
        }

        return [
            'success' => true,
            'data' => $this->patientModel->find($id),
        ];

    }
}
