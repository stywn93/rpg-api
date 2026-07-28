<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVQueue extends Migration
{
    public function up()
    {
        $this->db->query("CREATE OR REPLACE VIEW v_queues AS
            SELECT 
                q.id AS queue_id,
                date_format(`q`.`tanggal_kunjungan`,'%d %M %Y') AS `tanggal_kunjungan`,
                q.nomor_antrian,
                q.status,
                p.nama AS nama_pasien,
                concat(timestampdiff(YEAR,`p`.`tanggal_lahir`,curdate()),' tahun ',(timestampdiff(MONTH,`p`.`tanggal_lahir`,curdate()) % 12),' bulan') AS `usia`,
                u.name AS nama_parent,
                GROUP_CONCAT(st.nama_layanan ORDER BY st.nama_layanan SEPARATOR ', ') AS layanan
                FROM queue q
                LEFT JOIN patients p ON q.patient_id = p.id
                LEFT JOIN users u    ON p.parent_id  = u.id
                LEFT JOIN queue_service_types qst ON q.id = qst.queue_id
                LEFT JOIN service_types st ON qst.service_type_id = st.id
                GROUP BY q.id, q.tanggal_kunjungan, q.nomor_antrian, q.status, p.nama, u.name
                ORDER BY q.tanggal_kunjungan, q.nomor_antrian");
    }

    public function down()
    {
        $this->db->query("DROP VIEW IF EXISTS v_queues");
    }
}
