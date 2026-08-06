<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitModel extends Model
{
    protected $table            = 'visits';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'patient_id',
        'visit_date',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'patient_id' => 'required|integer',
        'visit_date' => 'permit_empty|valid_date',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Custom Query Join Patient
    |--------------------------------------------------------------------------
    */

    public function getPaginatedWithPatient(int $perPage = 10, int $page = 1): array
    {
        return $this->buildVisitWithPatientQuery()->paginate($perPage, 'default', $page);
    }

    public function findWithPatient(int $id): ?array
    {
        return $this->buildVisitWithPatientQuery()
            ->where('visits.id', $id)
            ->first();
    }

    private function buildVisitWithPatientQuery()
    {
        return $this->select('visits.*, patients.name as patient_name')
            ->join('patients', 'patients.id = visits.patient_id', 'left');
    }
}
