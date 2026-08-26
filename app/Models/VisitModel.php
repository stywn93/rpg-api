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
        'visit_status',
        'queue_number',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'patient_id'   => 'required|integer',
        'visit_date'   => 'permit_empty|valid_date',
        'queue_number' => 'permit_empty|max_length[10]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Generate next queue number RPG-01..RPG-99 for given date.
     * Returns null when quota 99 reached.
     * ponytail: COUNT+1 is race-prone without FOR UPDATE; add row lock if needed.
     */
    public function nextQueueNumber(string $date): ?string
    {
        $count = $this->where('visit_date', $date)->countAllResults();
        if ($count >= 99) {
            return null;
        }

        return sprintf('RPG-%02d', $count + 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Query Join Patient
    |--------------------------------------------------------------------------
    */

    public function getPaginatedWithPatient(int $perPage = 10, int $page = 1): array
    {
        return $this->buildVisitWithPatientQuery()->paginate($perPage, 'default', $page);
    }

    public function getPaginatedByParentWithPatient(int $parentId, int $perPage = 10, int $page = 1): array
    {
        return $this->buildVisitWithPatientQuery()
            ->where('patients.user_id', $parentId)
            ->paginate($perPage, 'default', $page);
    }

    public function findWithPatient(int $id): ?array
    {
        return $this->buildVisitWithPatientQuery()
            ->where('visits.id', $id)
            ->first();
    }

    private function buildVisitWithPatientQuery()
    {
        return $this->select(
            "visits.*, patients.name as patient_name,
            users.name as parent_name,
            CASE patients.gender_code
                WHEN 'L' THEN 'Laki-laki'
                WHEN 'P' THEN 'Perempuan'
            END AS patient_gender,
            CONCAT(
                TIMESTAMPDIFF(YEAR, patients.dob, CURRENT_DATE()),
                ' tahun ',
                MOD(TIMESTAMPDIFF(MONTH, patients.dob, CURRENT_DATE()), 12),
                ' bulan'
            ) AS age"
        )
            ->join('patients', 'patients.id = visits.patient_id', 'left')
            ->join('users', 'users.id = patients.user_id', 'left');
    }
}
