<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveUnusedPatientColumns extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('patients')) {
            return;
        }

        $this->dropIndexIfExists('patients', 'patients_nik_unique');
        $this->dropColumnIfExists('patients', 'nik');
        $this->dropColumnIfExists('patients', 'kecamatan');
        $this->dropColumnIfExists('patients', 'desa');
        $this->dropColumnIfExists('patients', 'berat_lahir');
    }

    public function down()
    {
        if (! $this->db->tableExists('patients')) {
            return;
        }

        if (! $this->db->fieldExists('nik', 'patients')) {
            $this->db->query('ALTER TABLE `patients` ADD `nik` VARCHAR(20) NULL AFTER `parent_id`');
        }

        if (! $this->db->fieldExists('kecamatan', 'patients')) {
            $this->db->query('ALTER TABLE `patients` ADD `kecamatan` VARCHAR(100) NOT NULL AFTER `alamat`');
        }

        if (! $this->db->fieldExists('desa', 'patients')) {
            $this->db->query('ALTER TABLE `patients` ADD `desa` VARCHAR(100) NOT NULL AFTER `kecamatan`');
        }

        if (! $this->db->fieldExists('berat_lahir', 'patients')) {
            $this->db->query(
                'ALTER TABLE `patients` ADD `berat_lahir` DECIMAL(5,2) NULL COMMENT \'dalam kg\' AFTER `desa`'
            );
        }

        $this->addUniqueIndexIfMissing('patients', 'patients_nik_unique', 'nik');
    }

    private function dropColumnIfExists(string $table, string $column): void
    {
        if ($this->db->fieldExists($column, $table)) {
            $this->db->query(sprintf('ALTER TABLE `%s` DROP COLUMN `%s`', $table, $column));
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
            $this->db->query(sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $indexName));
        }
    }

    private function addUniqueIndexIfMissing(string $table, string $indexName, string $column): void
    {
        if (! $this->db->fieldExists($column, $table)) {
            return;
        }

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
                sprintf('ALTER TABLE `%s` ADD UNIQUE KEY `%s` (`%s`)', $table, $indexName, $column)
            );
        }
    }
}
