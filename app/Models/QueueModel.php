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
        'status'            => 'required|in_list[booked,checked_in,called,served,finished,no_show,cancelled]',
    ];

    protected $skipValidation = false;

    public function getQueueListWithPatient(int $perPage = 10): array
    {
        return $this->select(
            "queue.id, queue.patient_id AS id_pasien,
            patients.nama AS nama_lengkap,
            patients.jenis_kelamin,
            TIMESTAMPDIFF(YEAR, patients.tanggal_lahir, CURDATE()) AS usia,
            queue.tanggal_kunjungan,
            CONCAT('Q-', LPAD(queue.id, 4, '0')) AS kode_antrian"
        )
            ->join('patients', 'patients.id = queue.patient_id', 'left')
            ->where('patients.deleted_at', null)
            ->orderBy('queue.tanggal_kunjungan', 'DESC')
            ->orderBy('queue.id', 'DESC')
            ->paginate($perPage);
    }
}
