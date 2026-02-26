<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveUniqueFromDaySchedules extends Migration
{
    public function up()
    {
        $this->forge->dropKey('schedules', 'schedules_hari_unique');
    }

    public function down()
    {
        $this->forge->addUniqueKey('hari', 'schedules_hari_unique');
        $this->forge->processIndexes('schedules');
    }
}
