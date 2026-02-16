<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientModel extends Model
{
    protected $table            = 'patients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'parent_id',
        'nik',
        'no_kk',
        'nama',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'kecamatan',
        'desa',
        'berat_lahir',
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
    public function getWithParent($id = null)
    {
        $builder = $this->select('patients.*, users.name as parent_name, users.email as parent_email')
            ->join('users', 'users.id = patients.parent_id');

        if ($id !== null) {
            return $builder->where('patients.id', $id)->first();
        }

        return $builder->findAll();
    }
}