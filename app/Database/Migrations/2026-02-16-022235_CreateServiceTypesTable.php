<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServiceTypesTable extends Migration
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
            'nama_layanan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'durasi_estimasi_menit' => [
                'type'       => 'INT',
                'constraint' => 5,
                'comment'    => 'Estimasi durasi dalam menit',
            ],
            'aktif' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
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
        $this->forge->createTable('service_types');
    }

    public function down()
    {
        $this->forge->dropTable('service_types');
    }
}