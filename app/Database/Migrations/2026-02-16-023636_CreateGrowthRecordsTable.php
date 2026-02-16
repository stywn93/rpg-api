<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGrowthRecordsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'patient_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'berat_badan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'comment'    => 'Dalam kg',
            ],
            'tinggi_badan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'comment'    => 'Dalam cm',
            ],
            'lingkar_lengan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Dalam cm (LILA)',
            ],
            'tanggal_pemeriksaan' => [
                'type' => 'DATE',
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('patient_id');

        $this->forge->addForeignKey(
            'patient_id',
            'patients',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('growth_records');
    }

    public function down()
    {
        $this->forge->dropTable('growth_records');
    }
}