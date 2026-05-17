<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterGrowthRecordsExaminationFields extends Migration
{
    public function up()
    {
        $fieldsToAdd = [
            'status_gizi' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'tinggi_badan',
            ],
            'keadaan_umum' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'status_gizi',
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'tanggal_pemeriksaan',
            ],
        ];

        $this->forge->addColumn('growth_records', $fieldsToAdd);

        if ($this->db->fieldExists('catatan', 'growth_records')) {
            $this->db->query('UPDATE growth_records SET keterangan = catatan WHERE catatan IS NOT NULL');
        }

        if ($this->db->fieldExists('lingkar_lengan', 'growth_records')) {
            $this->forge->dropColumn('growth_records', 'lingkar_lengan');
        }

        if ($this->db->fieldExists('catatan', 'growth_records')) {
            $this->forge->dropColumn('growth_records', 'catatan');
        }
    }

    public function down()
    {
        $fieldsToAdd = [
            'lingkar_lengan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Dalam cm (LILA)',
                'after'      => 'tinggi_badan',
            ],
            'catatan' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'tanggal_pemeriksaan',
            ],
        ];

        $this->forge->addColumn('growth_records', $fieldsToAdd);

        if ($this->db->fieldExists('keterangan', 'growth_records')) {
            $this->db->query('UPDATE growth_records SET catatan = keterangan WHERE keterangan IS NOT NULL');
            $this->forge->dropColumn('growth_records', 'keterangan');
        }

        if ($this->db->fieldExists('status_gizi', 'growth_records')) {
            $this->forge->dropColumn('growth_records', 'status_gizi');
        }

        if ($this->db->fieldExists('keadaan_umum', 'growth_records')) {
            $this->forge->dropColumn('growth_records', 'keadaan_umum');
        }
    }
}
