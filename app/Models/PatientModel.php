<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientModel extends Model
{
    protected $table            = 'patients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'user_id',
        'name',
        'dob',
        'gender_code',
        'address',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'user_id'   => 'required|integer',
        'name'      => 'required|min_length[3]',
        'dob'       => 'required|valid_date',
        'gender_code' => 'required|in_list[L,P]',
        'address'   => 'required|min_length[5]',
    ];

    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Custom Query Join Parent
    |--------------------------------------------------------------------------
    */

    public function getPaginatedWithAge(int $perPage = 10, int $page = 1): array
    {
        return $this->buildPatientWithAgeQuery()->paginate($perPage, 'default', $page);
    }

    public function findWithAge(int $id): ?array
    {
        return $this->buildPatientWithAgeQuery()
            ->where('patients.id', $id)
            ->first();
    }

    public function getByParentWithAge(int $parentId, int $perPage = 10, int $page = 1): array
    {
        return $this->buildPatientWithAgeQuery()
            ->where('patients.user_id', $parentId)
            ->paginate($perPage, 'default', $page);
    }

    private function buildPatientWithAgeQuery()
    {
        return $this->select(
            "patients.*, users.name as parent_name, users.email as parent_email,
            CONCAT(
                TIMESTAMPDIFF(YEAR, patients.dob, CURRENT_DATE()),
                ' tahun ',
                MOD(TIMESTAMPDIFF(MONTH, patients.dob, CURRENT_DATE()), 12),
                ' bulan'
            ) AS age"
        )->join('users', 'users.id = patients.user_id', 'left');
    }

    // New Query Builder for v_patients view
    public function getAllFromView(?int $parentId = null): array
    {
        $builder = $this->db->table('v_patients');

        if ($parentId !== null) {
            $builder->where('user_id', $parentId);
        }

        return $builder->get()->getResultArray();
    }
}
