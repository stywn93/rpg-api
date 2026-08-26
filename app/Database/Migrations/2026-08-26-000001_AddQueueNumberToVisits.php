<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQueueNumberToVisits extends Migration
{
    public function up()
    {
        $fields = [
            'queue_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'visit_status',
            ],
        ];

        $this->forge->addColumn('visits', $fields);

        // Unique per day to prevent duplicate queue on same date
        // ponytail: raw SQL because forge addUniqueKey only works on createTable; ignore if exists
        try {
            $this->db->query('CREATE UNIQUE INDEX uq_visits_date_queue ON visits (visit_date, queue_number)');
        } catch (\Throwable $e) {
        }
    }

    public function down()
    {
        try {
            $this->forge->dropKey('visits', 'uq_visits_date_queue');
        } catch (\Throwable $e) {
            try {
                $this->db->query('DROP INDEX uq_visits_date_queue ON visits');
            } catch (\Throwable $e2) {
            }
        }
        $this->forge->dropColumn('visits', 'queue_number');
    }
}
