<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAlamatToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'alamat' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'phone',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'alamat');
    }
}
