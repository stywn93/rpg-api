<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueToDaySchedules extends Migration
{
    public function up()
    {
        // Pastikan kolom nik sudah ada dan bertipe VARCHAR(16)
        $this->forge->modifyColumn('schedules', [
            'hari' => [
                'type'       => 'ENUM',
                'constraint' => ['selasa', 'rabu', 'kamis'],
                'null'       => false,
            ],
        ]);

        // Tambahkan unique key
        $this->forge->addUniqueKey('hari', 'schedules_hari_unique');
        $this->forge->processIndexes('schedules');
    }

    public function down()
    {
        // Hapus unique key jika rollback
        $this->forge->dropKey('schedules', 'schedules_hari_unique');
    }
}
