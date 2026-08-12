<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVisitStatus extends Migration
{
    public function up()
    {
        $fields = [
            'visit_status' => [
                'type'    => "ENUM('waiting','present','in_assesment','finished')",
                'null'    => false,
                'default' => 'waiting',
            ],
        ];

        $this->forge->addColumn('visits', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('visits', 'visit_status');
    }
}
