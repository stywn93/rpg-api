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

    /*
    |--------------------------------------------------------------------------
    | Custom Query Join Patient (per visit_service)
    |--------------------------------------------------------------------------
    */

    public function getVisitServicesWithPatient(int $perPage = 10, int $page = 1): array
    {
        $select = "p.name AS patient_name,
            CONCAT(
                FLOOR(TIMESTAMPDIFF(MONTH, p.dob, CURRENT_DATE()) / 12),
                ' tahun ',
                MOD(TIMESTAMPDIFF(MONTH, p.dob, CURRENT_DATE()), 12),
                ' bulan'
            ) AS age,
            CASE p.gender_code
                WHEN 'L' THEN 'Laki-laki'
                WHEN 'P' THEN 'Perempuan'
            END AS gender,
            u.name AS parent_name,
            v.id AS visit_id,
            v.visit_date,
            ms.service_name AS service,
            vs.result,
            vs.performed_by,
            performer.name AS performed_by_name";

        return $this->select($select, false)
            ->from('patients p', true)
            ->join('users u', 'u.id = p.user_id')
            ->join('visits v', 'v.patient_id = p.id')
            ->join('visit_services vs', 'vs.visit_id = v.id')
            ->join('medical_services ms', 'ms.service_id = vs.service_id')
            ->join('users performer', 'performer.id = vs.performed_by', 'left')
            ->where('p.deleted_at', null)
            ->where('u.deleted_at', null)
            ->paginate($perPage, 'default', $page);
    }
}
