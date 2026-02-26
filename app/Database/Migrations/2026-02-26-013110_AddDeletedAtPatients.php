<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtPatients extends Migration
{
    public function up()
    {
        $this->forge->addColumn('patients', [
            'deleted_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'updated_at', // sesuaikan dengan struktur tabel Anda
            ],
        ]);
    }

    public function down()
    {
        //
    }
}
