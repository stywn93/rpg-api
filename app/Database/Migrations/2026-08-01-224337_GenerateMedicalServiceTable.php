<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMedicalServicesTable extends Migration
{
    public function up()
    {
        $this->forge->dropTable('medical_services', true);

        $this->forge->addField([
            'service_id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'service_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
        ]);

        $this->forge->addKey('service_id', true); // primary key

        $this->forge->createTable('medical_services', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_general_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('medical_services', true);
    }
}
