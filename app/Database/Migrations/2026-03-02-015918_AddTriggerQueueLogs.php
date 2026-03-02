<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTriggerQueueLogs extends Migration
{
    public function up()
    {
        // Hapus dulu jika sudah ada (aman saat migrate ulang)
        $this->db->query("DROP TRIGGER IF EXISTS before_insert_queuelogs");

        $this->db->query("
            CREATE TRIGGER before_insert_queuelogs
            BEFORE INSERT ON queue_logs
            FOR EACH ROW
            BEGIN
                DECLARE last_status VARCHAR(255);

                SELECT status_baru
                INTO last_status
                FROM queue_logs
                WHERE queue_id = NEW.queue_id
                ORDER BY id DESC
                LIMIT 1;

                IF last_status IS NOT NULL THEN
                    SET NEW.status_sebelumnya = last_status;
                END IF;

            END
        ");
    }

    public function down()
    {
        $this->db->query("DROP TRIGGER IF EXISTS before_insert_queuelogs");
    }
}
