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
        'kode_booking',
        'schedule_id',
        'patient_id',
        'nomor_antrian',
        'estimasi_dilayani',
        'status',
        'waktu_checkin',
        'waktu_dilayani',
        'waktu_selesai',
    ];

    protected $useTimestamps = false; // karena hanya created_at manual

    protected $validationRules = [
        'kode_booking'  => 'required|is_unique[queue.kode_booking,id,{id}]',
        'schedule_id'   => 'required|integer',
        'patient_id'    => 'required|integer',
        'nomor_antrian' => 'required|integer',
        'status'        => 'required|in_list[booked,checked_in,called,served,finished,no_show,cancelled]',
    ];

    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Join lengkap (Schedule + Patient)
    |--------------------------------------------------------------------------
    */
    public function getFullQueue($id = null)
    {
        $builder = $this->select('queue.*, 
                                  patients.nama as nama_pasien,
                                  schedules.tanggal,
                                  schedules.jam_mulai,
                                  service_types.nama_layanan')
            ->join('patients', 'patients.id = queue.patient_id')
            ->join('schedules', 'schedules.id = queue.schedule_id')
            ->join('service_types', 'service_types.id = schedules.service_type_id');

        if ($id !== null) {
            return $builder->where('queue.id', $id)->first();
        }

        return $builder->findAll();
    }

    /*
    |--------------------------------------------------------------------------
    | Get Nomor Antrian Terakhir
    |--------------------------------------------------------------------------
    */
    public function getLastQueueNumber($scheduleId)
    {
        return $this->where('schedule_id', $scheduleId)
            ->selectMax('nomor_antrian')
            ->first();
    }
}