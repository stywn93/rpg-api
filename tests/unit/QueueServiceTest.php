<?php

use App\Models\QueueModel;
use App\Models\ServiceTypeModel;
use App\Services\QueueService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class QueueServiceTest extends CIUnitTestCase
{
    public function testListPassesPaginationAndFiltersToModel(): void
    {
        $service = new QueueService();

        $queueModel = $this->getMockBuilder(QueueModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQueueListWithPatient'])
            ->getMock();
        $serviceTypeModel = $this->getMockBuilder(ServiceTypeModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['whereIn', 'findAll'])
            ->getMock();

        $queueModel->expects($this->once())
            ->method('getQueueListWithPatient')
            ->with(15, '2026-05-15', 'booked', 'Budi', [2, 5])
            ->willReturn([
                [
                    'id' => 1,
                    'service_type_ids' => '2,5',
                    'nama_pasien' => 'Budi Santoso',
                    'status' => 'booked',
                ],
            ]);

        $serviceTypeModel->expects($this->once())
            ->method('whereIn')
            ->with('id', [2, 5])
            ->willReturnSelf();

        $serviceTypeModel->expects($this->once())
            ->method('findAll')
            ->willReturn([
                ['id' => 2, 'nama_layanan' => 'Imunisasi'],
                ['id' => 5, 'nama_layanan' => 'Konsultasi'],
            ]);

        $this->setPrivateProperty($service, 'queueModel', $queueModel);
        $this->setPrivateProperty($service, 'serviceTypeModel', $serviceTypeModel);

        $result = $service->list(15, '2026-05-15', 'booked', 'Budi', [2, 5]);

        $this->assertSame([
            [
                'id' => 1,
                'service_type_ids' => '2,5',
                'service_type_id_list' => [2, 5],
                'service_types' => [
                    ['id' => 2, 'nama_layanan' => 'Imunisasi'],
                    ['id' => 5, 'nama_layanan' => 'Konsultasi'],
                ],
                'service_type_names' => ['Imunisasi', 'Konsultasi'],
                'nama_pasien' => 'Budi Santoso',
                'status' => 'booked',
            ],
        ], $result);
    }

    public function testCreateGeneratesDailyQueueNumberAndReturnsInsertedQueue(): void
    {
        $service = new QueueService();

        $queueModel = $this->getMockBuilder(QueueModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insertWithDailyQueueNumber'])
            ->getMock();
        $serviceTypeModel = $this->getMockBuilder(ServiceTypeModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['whereIn', 'findAll'])
            ->getMock();

        $whereInCalls = 0;
        $serviceTypeModel->expects($this->exactly(2))
            ->method('whereIn')
            ->willReturnCallback(function ($field, $ids) use (&$whereInCalls, $serviceTypeModel) {
                $whereInCalls++;
                $this->assertSame('id', $field);
                $this->assertSame([2, 5], $ids);

                return $serviceTypeModel;
            });

        $serviceTypeModel->expects($this->exactly(2))
            ->method('findAll')
            ->willReturnOnConsecutiveCalls(
                [
                    ['id' => 2, 'nama_layanan' => 'Imunisasi'],
                    ['id' => 5, 'nama_layanan' => 'Konsultasi'],
                ],
                [
                    ['id' => 2, 'nama_layanan' => 'Imunisasi'],
                    ['id' => 5, 'nama_layanan' => 'Konsultasi'],
                ]
            );

        $queueModel->expects($this->once())
            ->method('insertWithDailyQueueNumber')
            ->with([
                'tanggal_kunjungan' => '2026-05-02',
                'patient_id' => 10,
                'service_type_ids' => '2,5',
                'status' => 'booked',
            ])
            ->willReturn([
                'id' => 15,
                'tanggal_kunjungan' => '2026-05-02',
                'patient_id' => 10,
                'service_type_ids' => '2,5',
                'nomor_antrian' => 1,
                'status' => 'booked',
            ]);

        $this->setPrivateProperty($service, 'queueModel', $queueModel);
        $this->setPrivateProperty($service, 'serviceTypeModel', $serviceTypeModel);

        $result = $service->create([
            'tanggal_kunjungan' => '2026-05-02',
            'patient_id' => '10',
            'service_type_ids' => '2, 5,2',
            'status' => 'booked',
            'nomor_antrian' => 99,
        ]);

        $this->assertSame([
            'id' => 15,
            'tanggal_kunjungan' => '2026-05-02',
            'patient_id' => 10,
            'service_type_ids' => '2,5',
            'service_type_id_list' => [2, 5],
            'service_types' => [
                ['id' => 2, 'nama_layanan' => 'Imunisasi'],
                ['id' => 5, 'nama_layanan' => 'Konsultasi'],
            ],
            'service_type_names' => ['Imunisasi', 'Konsultasi'],
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
        $serviceTypeModel = $this->getMockBuilder(ServiceTypeModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['whereIn', 'findAll'])
            ->getMock();

        $serviceTypeModel->expects($this->once())
            ->method('whereIn')
            ->with('id', [2])
            ->willReturnSelf();

        $serviceTypeModel->expects($this->once())
            ->method('findAll')
            ->willReturn([
                ['id' => 2, 'nama_layanan' => 'Imunisasi'],
            ]);

        $queueModel->expects($this->once())
            ->method('insertWithDailyQueueNumber')
            ->willReturn(false);

        $queueModel->expects($this->once())
            ->method('errors')
            ->willReturn([
                'patient_id' => 'The patient_id field is required.',
            ]);

        $this->setPrivateProperty($service, 'queueModel', $queueModel);
        $this->setPrivateProperty($service, 'serviceTypeModel', $serviceTypeModel);

        $result = $service->create([
            'tanggal_kunjungan' => '2026-05-02',
            'patient_id' => 10,
            'service_type_ids' => '2',
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
        $serviceTypeModel = $this->getMockBuilder(ServiceTypeModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['whereIn', 'findAll'])
            ->getMock();

        $queueModel->expects($this->once())
            ->method('getQueueDetailWithPatient')
            ->with(15)
            ->willReturn([
                'id' => 15,
                'patient_id' => 10,
                'service_type_ids' => '2,5',
                'nomor' => 1,
                'nomor_antrian' => 1,
                'tanggal_kunjungan' => '2026-05-02',
                'status' => 'booked',
                'nama_lengkap' => 'Budi Santoso',
                'usia' => '3 tahun 2 bulan',
                'nama_orang_tua' => 'Siti Aminah',
                'alamat' => 'Jl. Melati No. 10',
            ]);

        $serviceTypeModel->expects($this->once())
            ->method('whereIn')
            ->with('id', [2, 5])
            ->willReturnSelf();

        $serviceTypeModel->expects($this->once())
            ->method('findAll')
            ->willReturn([
                ['id' => 2, 'nama_layanan' => 'Imunisasi'],
                ['id' => 5, 'nama_layanan' => 'Konsultasi'],
            ]);

        $this->setPrivateProperty($service, 'queueModel', $queueModel);
        $this->setPrivateProperty($service, 'serviceTypeModel', $serviceTypeModel);

        $result = $service->find('15');

        $this->assertSame([
            'id' => 15,
            'patient_id' => 10,
            'service_type_ids' => '2,5',
            'service_type_id_list' => [2, 5],
            'service_types' => [
                ['id' => 2, 'nama_layanan' => 'Imunisasi'],
                ['id' => 5, 'nama_layanan' => 'Konsultasi'],
            ],
            'service_type_names' => ['Imunisasi', 'Konsultasi'],
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

    public function testUpdateNormalizesServicesAndReturnsEnrichedQueue(): void
    {
        $service = new QueueService();

        $queueModel = $this->getMockBuilder(QueueModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find', 'update', 'getQueueDetailWithPatient'])
            ->getMock();
        $serviceTypeModel = $this->getMockBuilder(ServiceTypeModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['whereIn', 'findAll'])
            ->getMock();

        $queueModel->expects($this->once())
            ->method('find')
            ->with(15)
            ->willReturn([
                'id' => 15,
            ]);

        $queueModel->expects($this->once())
            ->method('update')
            ->with(15, [
                'service_type_ids' => '2,5',
                'status' => 'called',
            ])
            ->willReturn(true);

        $queueModel->expects($this->once())
            ->method('getQueueDetailWithPatient')
            ->with(15)
            ->willReturn([
                'id' => 15,
                'patient_id' => 10,
                'service_type_ids' => '2,5',
                'nomor' => 1,
                'nomor_antrian' => 1,
                'tanggal_kunjungan' => '2026-05-02',
                'status' => 'called',
            ]);

        $whereInCalls = 0;
        $serviceTypeModel->expects($this->exactly(2))
            ->method('whereIn')
            ->willReturnCallback(function ($field, $ids) use (&$whereInCalls, $serviceTypeModel) {
                $whereInCalls++;
                $this->assertSame('id', $field);
                $this->assertSame([2, 5], $ids);

                return $serviceTypeModel;
            });

        $serviceTypeModel->expects($this->exactly(2))
            ->method('findAll')
            ->willReturn([
                ['id' => 2, 'nama_layanan' => 'Imunisasi'],
                ['id' => 5, 'nama_layanan' => 'Konsultasi'],
            ]);

        $this->setPrivateProperty($service, 'queueModel', $queueModel);
        $this->setPrivateProperty($service, 'serviceTypeModel', $serviceTypeModel);

        $result = $service->update(15, [
            'layanan' => '2, 5',
            'status' => 'called',
        ]);

        $this->assertSame([
            'id' => 15,
            'patient_id' => 10,
            'service_type_ids' => '2,5',
            'service_type_id_list' => [2, 5],
            'service_types' => [
                ['id' => 2, 'nama_layanan' => 'Imunisasi'],
                ['id' => 5, 'nama_layanan' => 'Konsultasi'],
            ],
            'service_type_names' => ['Imunisasi', 'Konsultasi'],
            'nomor' => 1,
            'nomor_antrian' => 1,
            'tanggal_kunjungan' => '2026-05-02',
            'status' => 'called',
        ], $result);
    }

    public function testCreateReturnsValidationErrorWhenOneOrMoreServiceTypesDoNotExist(): void
    {
        $service = new QueueService();

        $queueModel = $this->getMockBuilder(QueueModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insertWithDailyQueueNumber'])
            ->getMock();
        $serviceTypeModel = $this->getMockBuilder(ServiceTypeModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['whereIn', 'findAll'])
            ->getMock();

        $queueModel->expects($this->never())
            ->method('insertWithDailyQueueNumber');

        $serviceTypeModel->expects($this->once())
            ->method('whereIn')
            ->with('id', [2, 7])
            ->willReturnSelf();

        $serviceTypeModel->expects($this->once())
            ->method('findAll')
            ->willReturn([
                ['id' => 2, 'nama_layanan' => 'Imunisasi'],
            ]);

        $this->setPrivateProperty($service, 'queueModel', $queueModel);
        $this->setPrivateProperty($service, 'serviceTypeModel', $serviceTypeModel);

        $result = $service->create([
            'tanggal_kunjungan' => '2026-05-02',
            'patient_id' => 10,
            'service_type_ids' => '2,7',
            'status' => 'booked',
        ]);

        $this->assertSame([
            'error' => [
                'service_type_ids' => 'one or more service types were not found',
            ],
            'code' => 400,
        ], $result);
    }
}
