<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RestructureQueueTable extends Migration
{
    private const STATUS_VALUES = "'booked','checked_in','called','served','finished','no_show','cancelled'";

    public function up()
    {
        if (! $this->db->tableExists('queue')) {
            return;
        }

        $this->dropForeignKeysByColumn('queue', 'schedule_id');
        $this->dropForeignKeysByColumn('queue', 'patient_id');

        $this->dropColumnIfExists('queue', 'kode_booking');
        $this->dropColumnIfExists('queue', 'schedule_id');
        $this->dropColumnIfExists('queue', 'patient_id');
        $this->dropColumnIfExists('queue', 'nomor_antrian');
        $this->dropColumnIfExists('queue', 'estimasi_dilayani');
        $this->dropColumnIfExists('queue', 'waktu_checkin');
        $this->dropColumnIfExists('queue', 'waktu_dilayani');
        $this->dropColumnIfExists('queue', 'waktu_selesai');
        $this->dropColumnIfExists('queue', 'created_at');

        if (! $this->db->fieldExists('tanggal_kunjungan', 'queue')) {
            $this->db->query('ALTER TABLE `queue` ADD `tanggal_kunjungan` DATE NOT NULL AFTER `id`');
        }

        if ($this->db->fieldExists('status', 'queue')) {
            $this->db->query(
                'ALTER TABLE `queue` MODIFY `status` ENUM(' . self::STATUS_VALUES . ') NOT NULL DEFAULT \'booked\''
            );
        } else {
            $this->db->query(
                'ALTER TABLE `queue` ADD `status` ENUM(' . self::STATUS_VALUES . ') NOT NULL DEFAULT \'booked\' AFTER `tanggal_kunjungan`'
            );
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('queue')) {
            return;
        }

        $this->dropColumnIfExists('queue', 'tanggal_kunjungan');

        if (! $this->db->fieldExists('kode_booking', 'queue')) {
            $this->db->query('ALTER TABLE `queue` ADD `kode_booking` VARCHAR(30) NOT NULL UNIQUE AFTER `id`');
        }

        if (! $this->db->fieldExists('schedule_id', 'queue')) {
            $this->db->query('ALTER TABLE `queue` ADD `schedule_id` INT(11) UNSIGNED NOT NULL AFTER `kode_booking`');
            $this->db->query('ALTER TABLE `queue` ADD KEY `queue_schedule_id_index` (`schedule_id`)');
        }

        if (! $this->db->fieldExists('patient_id', 'queue')) {
            $this->db->query('ALTER TABLE `queue` ADD `patient_id` INT(11) UNSIGNED NOT NULL AFTER `schedule_id`');
            $this->db->query('ALTER TABLE `queue` ADD KEY `queue_patient_id_index` (`patient_id`)');
        }

        if (! $this->db->fieldExists('nomor_antrian', 'queue')) {
            $this->db->query('ALTER TABLE `queue` ADD `nomor_antrian` INT(5) NOT NULL AFTER `patient_id`');
        }

        if (! $this->db->fieldExists('estimasi_dilayani', 'queue')) {
            $this->db->query('ALTER TABLE `queue` ADD `estimasi_dilayani` DATETIME NULL AFTER `nomor_antrian`');
        }

        if (! $this->db->fieldExists('waktu_checkin', 'queue')) {
            $this->db->query('ALTER TABLE `queue` ADD `waktu_checkin` DATETIME NULL AFTER `status`');
        }

        if (! $this->db->fieldExists('waktu_dilayani', 'queue')) {
            $this->db->query('ALTER TABLE `queue` ADD `waktu_dilayani` DATETIME NULL AFTER `waktu_checkin`');
        }

        if (! $this->db->fieldExists('waktu_selesai', 'queue')) {
            $this->db->query('ALTER TABLE `queue` ADD `waktu_selesai` DATETIME NULL AFTER `waktu_dilayani`');
        }

        if (! $this->db->fieldExists('created_at', 'queue')) {
            $this->db->query('ALTER TABLE `queue` ADD `created_at` DATETIME NULL AFTER `waktu_selesai`');
        }

        $this->db->query(
            'ALTER TABLE `queue` MODIFY `status` ENUM(' . self::STATUS_VALUES . ') NOT NULL DEFAULT \'booked\''
        );

        $this->addForeignKeyIfMissing(
            'queue',
            'queue_schedule_id_foreign',
            'schedule_id',
            'schedules',
            'id'
        );

        $this->addForeignKeyIfMissing(
            'queue',
            'queue_patient_id_foreign',
            'patient_id',
            'patients',
            'id'
        );
    }

    private function dropColumnIfExists(string $table, string $column): void
    {
        if ($this->db->fieldExists($column, $table)) {
            $this->db->query(sprintf('ALTER TABLE `%s` DROP COLUMN `%s`', $table, $column));
        }
    }

    private function dropForeignKeysByColumn(string $table, string $column): void
    {
        if (! $this->db->fieldExists($column, $table)) {
            return;
        }

        $database = $this->db->getDatabase();
        $query    = $this->db->query(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $table, $column]
        );

        foreach ($query->getResultArray() as $row) {
            $this->db->query(
                sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $row['CONSTRAINT_NAME'])
            );
        }
    }

    private function addForeignKeyIfMissing(
        string $table,
        string $constraintName,
        string $column,
        string $referencedTable,
        string $referencedColumn
    ): void {
        if (! $this->db->fieldExists($column, $table)) {
            return;
        }

        $database = $this->db->getDatabase();
        $query    = $this->db->query(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $table, $constraintName]
        );

        if ($query->getNumRows() === 0) {
            $this->db->query(
                sprintf(
                    'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`) ON DELETE CASCADE ON UPDATE CASCADE',
                    $table,
                    $constraintName,
                    $column,
                    $referencedTable,
                    $referencedColumn
                )
            );
        }
    }
}
