<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQueueLogsTable extends Migration
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
            'queue_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status_sebelumnya' => [
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
            ],
            'status_baru' => [
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
            ],
            'changed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'changed_at' => [
                'type'    => 'DATETIME',
                'default' => date('Y-m-d H:i:s'),
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('queue_id');
        $this->forge->addKey('changed_by');

        $this->forge->addForeignKey(
            'queue_id',
            'queue',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'changed_by',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('queue_logs');
    }

    public function down()
    {
        $this->forge->dropTable('queue_logs');
    }
}