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

    public function getVisitServicesWithPatient(int $perPage = 10, int $page = 1, array $filters = []): array
    {
        $select = "p.id AS patient_id,
            p.name AS patient_name,
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
            v.visit_status,
            GROUP_CONCAT(ms.service_name SEPARATOR ', ') AS services,
            GROUP_CONCAT(ms.service_id SEPARATOR ', ') AS service_id
            ";

        $query = $this->select($select, false)
            ->from('patients p', true)
            ->join('users u', 'u.id = p.user_id')
            ->join('visits v', 'v.patient_id = p.id')
            ->join('visit_services vs', 'vs.visit_id = v.id')
            ->join('medical_services ms', 'ms.service_id = vs.service_id')
            ->where('p.deleted_at', null)
            ->where('u.deleted_at', null);

        if (!empty($filters['visit_id'])) {
            $query->where('vs.visit_id', $filters['visit_id']);
        }
        if (!empty($filters['service_id'])) {
            $query->where('vs.service_id', $filters['service_id']);
        }
        if (!empty($filters['performed_by'])) {
            $query->where('vs.performed_by', $filters['performed_by']);
        }
        if (!empty($filters['visit_date'])) {
            $query->where('v.visit_date', $filters['visit_date']);
        }
        if (!empty($filters['visit_status'])) {
            $query->where('v.visit_status', $filters['visit_status']);
        }
        if (!empty($filters['patient_name'])) {
            $query->like('p.name', $filters['patient_name']);
        }
        if (!empty($filters['patient_id'])) {
            $query->where('p.id', $filters['patient_id']);
        }
        if (!empty($filters['parent_id'])) {
            $query->where('u.id', $filters['parent_id']);
        }
        if (!empty($filters['gender'])) {
            $genderCode = match (strtolower($filters['gender'])) {
                'laki-laki' => 'L',
                'perempuan' => 'P',
                default     => $filters['gender'],
            };
            $query->where('p.gender_code', $genderCode);
        }

        return $query->groupBy('p.id, u.id, v.id')
            ->paginate($perPage, 'default', $page);
    }

    public function syncVisitServices(int $visitId, array $newServiceIds): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $oldServiceIds = $db->table('visit_services')
            ->select('service_id')
            ->where('visit_id', $visitId)
            ->get()
            ->getResultArray();
        $oldServiceIds = array_column($oldServiceIds, 'service_id');

        $toInsert = array_diff($newServiceIds, $oldServiceIds);
        $toDelete = array_diff($oldServiceIds, $newServiceIds);

        if (!empty($toDelete)) {
            $db->table('visit_services')
                ->where('visit_id', $visitId)
                ->whereIn('service_id', $toDelete)
                ->delete();
        }

        if (!empty($toInsert)) {
            $insertBatch = array_map(fn($sid) => [
                'visit_id'   => $visitId,
                'service_id' => $sid,
            ], $toInsert);

            $db->table('visit_services')->insertBatch($insertBatch);
        }

        $db->transComplete();

        return $db->transStatus();
    }

    public function getServicesWithDetail(int $visitId): array
    {
        return $this->db->table('visit_services vs')
            ->select('s.service_id, s.service_name')
            ->join('medical_services s', 's.service_id = vs.service_id')
            ->where('vs.visit_id', $visitId)
            ->get()
            ->getResultArray();
    }

}