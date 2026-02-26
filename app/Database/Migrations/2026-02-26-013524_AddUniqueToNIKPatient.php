<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueToNIKPatient extends Migration
{
    public function up()
    {
        // Pastikan kolom nik sudah ada dan bertipe VARCHAR(16)
        $this->forge->modifyColumn('patients', [
            'nik' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
        ]);

        // Tambahkan unique key
        $this->forge->addUniqueKey('nik', 'patients_nik_unique');
        $this->forge->processIndexes('patients');
    }

    public function down()
    {
        // Hapus unique key jika rollback
        $this->forge->dropKey('patients', 'patients_nik_unique');
    }
}
