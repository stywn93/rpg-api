<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPatientIdToQueueTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('queue') || ! $this->db->tableExists('patients')) {
            return;
        }

        if (! $this->db->fieldExists('patient_id', 'queue')) {
            $after = $this->db->fieldExists('tanggal_kunjungan', 'queue')
                ? ' AFTER `tanggal_kunjungan`'
                : '';

            $this->db->query(
                'ALTER TABLE `queue` ADD `patient_id` INT(11) UNSIGNED NULL' . $after
            );
        }

        $this->addIndexIfMissing('queue', 'queue_patient_id_index', 'patient_id');

        $this->addForeignKeyIfMissing(
            'queue',
            'queue_patient_id_foreign',
            'patient_id',
            'patients',
            'id'
        );
    }

    public function down()
    {
        if (! $this->db->tableExists('queue')) {
            return;
        }

        $this->dropForeignKeyIfExists('queue', 'queue_patient_id_foreign');
        $this->dropIndexIfExists('queue', 'queue_patient_id_index');

        if ($this->db->fieldExists('patient_id', 'queue')) {
            $this->db->query('ALTER TABLE `queue` DROP COLUMN `patient_id`');
        }
    }

    private function addForeignKeyIfMissing(
        string $table,
        string $constraintName,
        string $column,
        string $referencedTable,
        string $referencedColumn
    ): void {
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

    private function dropForeignKeyIfExists(string $table, string $constraintName): void
    {
        $database = $this->db->getDatabase();
        $query    = $this->db->query(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = \'FOREIGN KEY\'',
            [$database, $table, $constraintName]
        );

        if ($query->getNumRows() > 0) {
            $this->db->query(
                sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $constraintName)
            );
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $database = $this->db->getDatabase();
        $query    = $this->db->query(
            'SELECT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND INDEX_NAME = ?',
            [$database, $table, $indexName]
        );

        if ($query->getNumRows() > 0) {
            $this->db->query(
                sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $indexName)
            );
        }
    }

    private function addIndexIfMissing(string $table, string $indexName, string $column): void
    {
        $database = $this->db->getDatabase();
        $query    = $this->db->query(
            'SELECT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND INDEX_NAME = ?',
            [$database, $table, $indexName]
        );

        if ($query->getNumRows() === 0) {
            $this->db->query(
                sprintf('ALTER TABLE `%s` ADD KEY `%s` (`%s`)', $table, $indexName, $column)
            );
        }
    }
}
