<?php

namespace App\Services;

use App\Models\PatientModel;

class PatientService
{
    protected $patientModel;
    protected array $allowedFields = [
        'parent_id',
        'no_kk',
        'nama',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
    ];

    public function __construct()
    {
        $this->patientModel = new PatientModel();
    }

    public function list($perPage = 10)
    {
        return $this->patientModel->getPaginatedWithAge((int) $perPage);
    }

    public function find($id)
    {
        return $this->patientModel->findWithAge((int) $id);
    }

    public function create($data)
    {
        $payload = $this->preparePayload($data);
        if ($payload === []) {
            return [
                'error' => [
                    'payload' => 'patient payload is empty or invalid',
                ],
                'code' => 400,
            ];
        }

        $inserted = $this->patientModel->insert($payload);
        if ($inserted === false) {
            return [
                'error' => $this->patientModel->errors(),
                'code' => 400,
            ];
        }
        return $this->patientModel->findWithAge((int) $this->patientModel->getInsertID());
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

        $payload = $this->preparePayload($data);
        if ($payload === []) {
            return [
                'error' => [
                    'payload' => 'patient payload is empty or invalid',
                ],
                'code' => 400,
            ];
        }

        $updated = $this->patientModel->update($id, $payload);
        if ($updated === false) {
            return [
                'error' => $this->patientModel->errors(),
                'code' => 400,
            ];
        }

        return $this->patientModel->findWithAge((int) $id);
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
        return $this->patientModel->getByParentWithAge((int) $parentID, 10);
    }

    private function preparePayload($data): array
    {
        if (! is_array($data)) {
            return [];
        }

        $payload = array_intersect_key($data, array_flip($this->allowedFields));

        if (array_key_exists('parent_id', $payload)) {
            $payload['parent_id'] = (int) $payload['parent_id'];
        }

        foreach (['no_kk', 'nama', 'tanggal_lahir', 'jenis_kelamin', 'alamat'] as $field) {
            if (array_key_exists($field, $payload) && is_string($payload[$field])) {
                $payload[$field] = trim($payload[$field]);
            }
        }

        return $payload;
    }
}
