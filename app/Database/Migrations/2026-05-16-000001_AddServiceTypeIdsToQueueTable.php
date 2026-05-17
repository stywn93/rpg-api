<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddServiceTypeIdsToQueueTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('queue')) {
            return;
        }

        if (! $this->db->fieldExists('service_type_ids', 'queue')) {
            $after = $this->db->fieldExists('patient_id', 'queue')
                ? ' AFTER `patient_id`'
                : '';

            $this->db->query(
                'ALTER TABLE `queue` ADD `service_type_ids` VARCHAR(255) NOT NULL' . $after
            );
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('queue')) {
            return;
        }

        if ($this->db->fieldExists('service_type_ids', 'queue')) {
            $this->db->query('ALTER TABLE `queue` DROP COLUMN `service_type_ids`');
        }
    }
}
