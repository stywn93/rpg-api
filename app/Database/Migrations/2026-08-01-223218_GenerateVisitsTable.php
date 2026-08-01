<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVisitsTable extends Migration
{
    public function up()
    {
        $this->forge->dropTable('visits', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'patient_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'visit_date' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addKey('id', true);       // primary key
        $this->forge->addKey('patient_id');      // regular index for the FK

        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('visits', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_general_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('visits', true);
    }
}
