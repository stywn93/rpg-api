<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitServiceModel extends Model
{
    protected $table            = 'visit_services';
    protected $primaryKey       = 'visit_service_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'visit_id',
        'service_id',
        'result',
        'performed_by',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'visit_id'     => 'required|integer',
        'service_id'   => 'required|integer',
        'result'       => 'permit_empty|max_length[100]',
        'performed_by' => 'permit_empty|integer',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Custom Query Join Details
    |--------------------------------------------------------------------------
    */

    public function getPaginatedWithDetails(int $perPage = 10, int $page = 1): array
    {
        return $this->buildVisitServiceWithDetailsQuery()->paginate($perPage, 'default', $page);
    }

    public function findWithDetails(int $id): ?array
    {
        return $this->buildVisitServiceWithDetailsQuery()
            ->where('visit_services.visit_service_id', $id)
            ->first();
    }

    private function buildVisitServiceWithDetailsQuery()
    {
        return $this->select(
            "visit_services.*, medical_services.service_name,
            patients.name as patient_name, users.name as performed_by_name"
        )
            ->join('medical_services', 'medical_services.service_id = visit_services.service_id', 'left')
            ->join('visits', 'visits.id = visit_services.visit_id', 'left')
            ->join('patients', 'patients.id = visits.patient_id', 'left')
            ->join('users', 'users.id = visit_services.performed_by', 'left');
    }
}
