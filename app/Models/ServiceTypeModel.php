<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceTypeModel extends Model
{
    protected $table            = 'service_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'nama_layanan',
        'deskripsi',
        'durasi_estimasi_menit',
        'aktif',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nama_layanan'           => 'required|min_length[3]',
        'durasi_estimasi_menit'  => 'required|integer',
        'aktif'                  => 'permit_empty|in_list[0,1]',
    ];

    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Scope Aktif
    |--------------------------------------------------------------------------
    */
    public function getActive()
    {
        return $this->where('aktif', 1)->findAll();
    }
}