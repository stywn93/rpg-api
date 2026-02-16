<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleModel extends Model
{
    protected $table            = 'schedules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'kuota',
        'service_type_id',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'tanggal'         => 'required|valid_date',
        'jam_mulai'       => 'required',
        'jam_selesai'     => 'required',
        'kuota'           => 'required|integer',
        'service_type_id' => 'required|integer',
        'status'          => 'required|in_list[open,closed]',
    ];

    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Join Service Type
    |--------------------------------------------------------------------------
    */
    public function getWithService($id = null)
    {
        $builder = $this->select('schedules.*, service_types.nama_layanan')
            ->join('service_types', 'service_types.id = schedules.service_type_id');

        if ($id !== null) {
            return $builder->where('schedules.id', $id)->first();
        }

        return $builder->findAll();
    }

    /*
    |--------------------------------------------------------------------------
    | Get Jadwal yang Masih Open
    |--------------------------------------------------------------------------
    */
    public function getOpenSchedules()
    {
        return $this->where('status', 'open')
            ->where('tanggal >=', date('Y-m-d'))
            ->findAll();
    }
}