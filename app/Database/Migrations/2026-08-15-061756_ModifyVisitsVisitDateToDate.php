<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class ModifyVisitsVisitDateToDate extends Migration
{
    public function up()
    {
        $fields = [
            'visit_date' => [
                'type'    => 'DATE',
                'null'    => false,
                'default' => new RawSql('(CURRENT_DATE)'),
            ],
        ];

        $this->forge->modifyColumn('visits', $fields);
    }

    public function down()
    {
        $fields = [
            'visit_date' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ];

        $this->forge->modifyColumn('visits', $fields);
    }
}