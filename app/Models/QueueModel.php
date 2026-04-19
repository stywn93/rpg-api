<?php

namespace App\Models;

use CodeIgniter\Model;

class QueueModel extends Model
{
    protected $table            = 'queue';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'tanggal_kunjungan',
        'patient_id',
        'status',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'tanggal_kunjungan' => 'required|valid_date[Y-m-d]',
        'patient_id'        => 'permit_empty|integer|is_not_unique[patients.id]',
        'status'        => 'required|in_list[booked,checked_in,called,served,finished,no_show,cancelled]',
    ];

    protected $skipValidation = false;
}
