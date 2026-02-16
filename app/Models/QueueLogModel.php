<?php

namespace App\Models;

use CodeIgniter\Model;

class QueueLogModel extends Model
{
    protected $table            = 'queue_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'queue_id',
        'status_sebelumnya',
        'status_baru',
        'changed_by',
        'changed_at',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'queue_id'          => 'required|integer',
        'status_sebelumnya' => 'required|in_list[booked,checked_in,called,served,finished,no_show,cancelled]',
        'status_baru'       => 'required|in_list[booked,checked_in,called,served,finished,no_show,cancelled]',
        'changed_by'        => 'required|integer',
    ];

    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Join dengan user & queue
    |--------------------------------------------------------------------------
    */
    public function getLogsWithUser($queueId)
    {
        return $this->select('queue_logs.*, users.name as changed_by_name')
            ->join('users', 'users.id = queue_logs.changed_by')
            ->where('queue_id', $queueId)
            ->orderBy('changed_at', 'ASC')
            ->findAll();
    }
}