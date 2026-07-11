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
        'parent_id',
        'no_kk',
        'nama',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'parent_id'     => 'required|integer',
        'no_kk'         => 'required',
        'nama'          => 'required|min_length[3]',
        'tanggal_lahir' => 'required|valid_date',
        'jenis_kelamin' => 'required|in_list[L,P]',
    ];

    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Custom Query Join Parent
    |--------------------------------------------------------------------------
    */

    public function getPaginatedWithAge(int $perPage = 10): array
    {
        return $this->buildPatientWithAgeQuery()->paginate($perPage);
    }

    public function findWithAge(int $id): ?array
    {
        return $this->buildPatientWithAgeQuery()
            ->where('patients.id', $id)
            ->first();
    }

    public function getByParentWithAge(int $parentId, int $perPage = 10): array
    {
        return $this->buildPatientWithAgeQuery()
            ->where('patients.parent_id', $parentId)
            ->paginate($perPage);
    }

    private function buildPatientWithAgeQuery()
    {
        return $this->select(
            "patients.*, users.name as parent_name, users.email as parent_email,
            CONCAT(
                TIMESTAMPDIFF(YEAR, patients.tanggal_lahir, CURRENT_DATE()),
                ' tahun ',
                MOD(TIMESTAMPDIFF(MONTH, patients.tanggal_lahir, CURRENT_DATE()), 12),
                ' bulan'
            ) AS usia"
        )->join('users', 'users.id = patients.parent_id', 'left');
    }

    // New Query Builder for v_patients view
    public function getAllFromView(): array
    {
        return $this->db->table('v_patients')->get()->getResultArray();
    }
}
