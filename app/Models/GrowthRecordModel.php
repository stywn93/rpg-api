<?php

namespace App\Models;

use CodeIgniter\Model;

class GrowthRecordModel extends Model
{
    protected $table            = 'growth_records';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'patient_id',
        'berat_badan',
        'tinggi_badan',
        'lingkar_lengan',
        'tanggal_pemeriksaan',
        'catatan',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'patient_id'          => 'required|integer',
        'berat_badan'         => 'required|decimal',
        'tinggi_badan'        => 'required|decimal',
        'tanggal_pemeriksaan' => 'required|valid_date',
    ];

    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Riwayat berdasarkan pasien
    |--------------------------------------------------------------------------
    */
    public function getByPatient($patientId)
    {
        return $this->where('patient_id', $patientId)
            ->orderBy('tanggal_pemeriksaan', 'ASC')
            ->findAll();
    }
}