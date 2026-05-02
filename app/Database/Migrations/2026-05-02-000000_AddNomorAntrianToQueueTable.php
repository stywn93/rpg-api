<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNomorAntrianToQueueTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('queue')) {
            return;
        }

        if (! $this->db->fieldExists('nomor_antrian', 'queue')) {
            $after = $this->db->fieldExists('patient_id', 'queue')
                ? ' AFTER `patient_id`'
                : ' AFTER `tanggal_kunjungan`';

            $this->db->query(
                'ALTER TABLE `queue` ADD `nomor_antrian` INT(5) NULL' . $after
            );
        }

        $rows = $this->db->table('queue')
            ->select('id, tanggal_kunjungan')
            ->orderBy('tanggal_kunjungan', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $lastTanggal = null;
        $nomor = 0;

        foreach ($rows as $row) {
            if ($row['tanggal_kunjungan'] !== $lastTanggal) {
                $lastTanggal = $row['tanggal_kunjungan'];
                $nomor = 1;
            } else {
                $nomor++;
            }

            $this->db->table('queue')
                ->where('id', $row['id'])
                ->update(['nomor_antrian' => $nomor]);
        }

        $this->db->query('ALTER TABLE `queue` MODIFY `nomor_antrian` INT(5) NOT NULL');

        $this->addUniqueIndexIfMissing(
            'queue',
            'queue_tanggal_kunjungan_nomor_antrian_unique',
            ['tanggal_kunjungan', 'nomor_antrian']
        );
    }

    public function down()
    {
        if (! $this->db->tableExists('queue')) {
            return;
        }

        $this->dropIndexIfExists('queue', 'queue_tanggal_kunjungan_nomor_antrian_unique');

        if ($this->db->fieldExists('nomor_antrian', 'queue')) {
            $this->db->query('ALTER TABLE `queue` DROP COLUMN `nomor_antrian`');
        }
    }

    private function addUniqueIndexIfMissing(string $table, string $indexName, array $columns): void
    {
        $database = $this->db->getDatabase();
        $query = $this->db->query(
            'SELECT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND INDEX_NAME = ?
             LIMIT 1',
            [$database, $table, $indexName]
        );

        if ($query->getNumRows() > 0) {
            return;
        }

        $columnList = implode(
            ', ',
            array_map(static fn (string $column): string => sprintf('`%s`', $column), $columns)
        );

        $this->db->query(
            sprintf(
                'ALTER TABLE `%s` ADD CONSTRAINT `%s` UNIQUE (%s)',
                $table,
                $indexName,
                $columnList
            )
        );
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $database = $this->db->getDatabase();
        $query = $this->db->query(
            'SELECT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND INDEX_NAME = ?
             LIMIT 1',
            [$database, $table, $indexName]
        );

        if ($query->getNumRows() === 0) {
            return;
        }

        $this->db->query(
            sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $indexName)
        );
    }
}
