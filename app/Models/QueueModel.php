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
        'nomor_antrian',
        'status',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'tanggal_kunjungan' => 'required|valid_date[Y-m-d]',
        'patient_id'        => 'permit_empty|integer|is_not_unique[patients.id]',
        'nomor_antrian'     => 'permit_empty|integer|greater_than[0]',
        'status'            => 'required|in_list[booked,checked_in,called,served,finished,no_show,cancelled]',
    ];

    protected $skipValidation = false;

    public function getQueueListWithPatient(int $perPage = 10, ?string $tanggal = null): array
    {
        $builder = $this->buildQueuePatientQuery();

        if ($tanggal !== null && $tanggal !== '') {
            $builder->where('queue.tanggal_kunjungan', $tanggal);
        }

        return $builder
            ->orderBy('queue.tanggal_kunjungan', 'DESC')
            ->orderBy('queue.nomor_antrian', 'ASC')
            ->paginate($perPage);
    }

    public function getQueueDetailWithPatient(int $id): ?array
    {
        return $this->buildQueuePatientQuery()
            ->where('queue.id', $id)
            ->first();
    }

    public function insertWithDailyQueueNumber(array $payload)
    {
        $this->db->transBegin();

        $query = $this->db->query(
            'SELECT COALESCE(MAX(nomor_antrian), 0) AS last_number
             FROM `queue`
             WHERE `tanggal_kunjungan` = ?
             FOR UPDATE',
            [$payload['tanggal_kunjungan']]
        );

        $payload['nomor_antrian'] = ((int) ($query->getRow('last_number') ?? 0)) + 1;

        $inserted = $this->insert($payload);
        if ($inserted === false) {
            $this->db->transRollback();

            return false;
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();

            return false;
        }

        $insertId = $this->getInsertID();
        $this->db->transCommit();

        return $this->getQueueDetailWithPatient((int) $insertId);
    }

    private function buildQueuePatientQuery()
    {
        return $this->select(
            "queue.id,
            queue.patient_id,
            queue.nomor_antrian AS nomor,
            queue.nomor_antrian,
            queue.tanggal_kunjungan,
            queue.status,
            patients.nama AS nama_pasien,
            patients.nama AS nama_lengkap,
            patients.jenis_kelamin,
            CONCAT(
                TIMESTAMPDIFF(YEAR, patients.tanggal_lahir, queue.tanggal_kunjungan),
                ' tahun ',
                MOD(TIMESTAMPDIFF(MONTH, patients.tanggal_lahir, queue.tanggal_kunjungan), 12),
                ' bulan'
            ) AS usia,
            users.name AS nama_orang_tua,
            patients.alamat,
            CONCAT('Q-', LPAD(queue.id, 4, '0')) AS kode_referensi"
        )
            ->join('patients', 'patients.id = queue.patient_id', 'left')
            ->join('users', 'users.id = patients.parent_id', 'left')
            ->where('patients.deleted_at', null);
    }
}
