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
        'hari',
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
        'hari'            => 'required|in_list[selasa,rabu,kamis]',
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

    /*
    |--------------------------------------------------------------------------
    | Get Jadwal yang Masih Open
    |--------------------------------------------------------------------------
    */
}