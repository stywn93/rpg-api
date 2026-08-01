<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVisitServicesTable extends Migration
{
    public function up()
    {
        $this->forge->dropTable('visit_services', true);

        $this->forge->addField([
            'visit_service_id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'visit_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'service_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'result' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'performed_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('visit_service_id', true); // primary key
        $this->forge->addKey('visit_id');
        $this->forge->addKey('service_id');
        $this->forge->addKey('performed_by');

        $this->forge->addForeignKey('visit_id', 'visits', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('service_id', 'medical_services', 'service_id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('performed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('visit_services', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_general_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('visit_services', true);
    }
}
