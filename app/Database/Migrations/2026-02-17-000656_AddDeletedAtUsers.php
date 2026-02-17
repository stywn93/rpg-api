<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'deleted_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'updated_at', // sesuaikan dengan struktur tabel Anda
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'deleted_at');
    }
}
