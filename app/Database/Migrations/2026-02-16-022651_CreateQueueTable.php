<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQueueTable extends Migration
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
            'kode_booking' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'unique'     => true,
            ],
            'schedule_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'patient_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nomor_antrian' => [
                'type'       => 'INT',
                'constraint' => 5,
            ],
            'estimasi_dilayani' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'booked',
                    'checked_in',
                    'called',
                    'served',
                    'finished',
                    'no_show',
                    'cancelled'
                ],
                'default' => 'booked',
            ],
            'waktu_checkin' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'waktu_dilayani' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'waktu_selesai' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('schedule_id');
        $this->forge->addKey('patient_id');

        $this->forge->addForeignKey(
            'schedule_id',
            'schedules',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'patient_id',
            'patients',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('queue');
    }

    public function down()
    {
        $this->forge->dropTable('queue');
    }
}