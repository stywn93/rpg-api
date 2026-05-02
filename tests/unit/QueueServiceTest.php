<?php

use App\Models\QueueModel;
use App\Services\QueueService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class QueueServiceTest extends CIUnitTestCase
{
    public function testCreateGeneratesDailyQueueNumberAndReturnsInsertedQueue(): void
    {
        $service = new QueueService();

        $queueModel = $this->getMockBuilder(QueueModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insertWithDailyQueueNumber'])
            ->getMock();

        $queueModel->expects($this->once())
            ->method('insertWithDailyQueueNumber')
            ->with([
                'tanggal_kunjungan' => '2026-05-02',
                'patient_id' => 10,
                'status' => 'booked',
            ])
            ->willReturn([
                'id' => 15,
                'tanggal_kunjungan' => '2026-05-02',
                'patient_id' => 10,
                'nomor_antrian' => 1,
                'status' => 'booked',
            ]);

        $this->setPrivateProperty($service, 'queueModel', $queueModel);

        $result = $service->create([
            'tanggal_kunjungan' => '2026-05-02',
            'patient_id' => '10',
            'status' => 'booked',
            'nomor_antrian' => 99,
        ]);

        $this->assertSame([
            'id' => 15,
            'tanggal_kunjungan' => '2026-05-02',
            'patient_id' => 10,
            'nomor_antrian' => 1,
            'status' => 'booked',
        ], $result);
    }

    public function testCreateReturnsValidationErrorWhenInsertFails(): void
    {
        $service = new QueueService();

        $queueModel = $this->getMockBuilder(QueueModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insertWithDailyQueueNumber', 'errors'])
            ->getMock();

        $queueModel->expects($this->once())
            ->method('insertWithDailyQueueNumber')
            ->willReturn(false);

        $queueModel->expects($this->once())
            ->method('errors')
            ->willReturn([
                'patient_id' => 'The patient_id field is required.',
            ]);

        $this->setPrivateProperty($service, 'queueModel', $queueModel);

        $result = $service->create([
            'tanggal_kunjungan' => '2026-05-02',
            'patient_id' => 10,
            'status' => 'booked',
        ]);

        $this->assertSame([
            'error' => [
                'patient_id' => 'The patient_id field is required.',
            ],
            'code' => 400,
        ], $result);
    }

    public function testFindReturnsQueueDetailWithPatient(): void
    {
        $service = new QueueService();

        $queueModel = $this->getMockBuilder(QueueModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQueueDetailWithPatient'])
            ->getMock();

        $queueModel->expects($this->once())
            ->method('getQueueDetailWithPatient')
            ->with(15)
            ->willReturn([
                'id' => 15,
                'patient_id' => 10,
                'nomor' => 1,
                'nomor_antrian' => 1,
                'tanggal_kunjungan' => '2026-05-02',
                'status' => 'booked',
                'nama_lengkap' => 'Budi Santoso',
                'usia' => '3 tahun 2 bulan',
                'nama_orang_tua' => 'Siti Aminah',
                'alamat' => 'Jl. Melati No. 10',
            ]);

        $this->setPrivateProperty($service, 'queueModel', $queueModel);

        $result = $service->find('15');

        $this->assertSame([
            'id' => 15,
            'patient_id' => 10,
            'nomor' => 1,
            'nomor_antrian' => 1,
            'tanggal_kunjungan' => '2026-05-02',
            'status' => 'booked',
            'nama_lengkap' => 'Budi Santoso',
            'usia' => '3 tahun 2 bulan',
            'nama_orang_tua' => 'Siti Aminah',
            'alamat' => 'Jl. Melati No. 10',
        ], $result);
    }
}
