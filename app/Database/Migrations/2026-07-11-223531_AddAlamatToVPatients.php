<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAlamatToVPatients extends Migration
{
    public function up()
    {
        $this->db->query("CREATE OR REPLACE VIEW v_patients AS
            SELECT
                p.id,
                p.no_kk,
                p.nama,
                p.alamat,
                DATE_FORMAT(p.tanggal_lahir, '%d %M %Y') AS tanggal_lahir,
                IF(p.jenis_kelamin = 'L', 'Laki-laki', 'Perempuan') AS jenis_kelamin,
                CONCAT(
                    TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURRENT_DATE()),
                    ' tahun ',
                    MOD(TIMESTAMPDIFF(MONTH, p.tanggal_lahir, CURRENT_DATE()), 12),
                    ' bulan'
                ) AS usia,
                u.name AS parent_name,
                u.email
            FROM patients p
            LEFT JOIN users u ON p.parent_id = u.id");
    }

    public function down()
    {
        $this->db->query("DROP VIEW IF EXISTS v_patients");
    }
}
