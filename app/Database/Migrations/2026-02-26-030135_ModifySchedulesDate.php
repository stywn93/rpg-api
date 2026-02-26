<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifySchedulesDate extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('schedules', [
            'tanggal' => [
                'name'       => 'hari', // rename column
                'type'       => 'ENUM',
                'constraint' => ['selasa', 'rabu', 'kamis'],
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('schedules', [
            'hari' => [
                'name' => 'tanggal', // rollback rename
                'type' => 'DATE',
                'null' => false,
            ],
        ]);
    }
}
