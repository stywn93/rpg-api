<?php

use App\Models\GrowthRecordModel;
use App\Models\QueueModel;
use App\Services\GrowthRecordService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class GrowthRecordServiceTest extends CIUnitTestCase
{
    public function testCreateWithQueueIdMarksQueueAsFinished(): void
    {
        $service = new GrowthRecordService();

        $growthRecordModel = $this->getMockBuilder(GrowthRecordModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insert', 'getInsertID', 'find'])
            ->getMock();

        $queueModel = $this->getMockBuilder(QueueModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find', 'update'])
            ->getMock();

        $payload = [
            'patient_id' => 12,
            'berat_badan' => 10.5,
            'tinggi_badan' => 82.1,
            'status_gizi' => 'baik',
            'keadaan_umum' => 'sehat',
            'tanggal_pemeriksaan' => '2026-05-03',
            'keterangan' => 'normal',
            'queue_id' => 7,
        ];

        $growthRecordModel->expects($this->once())
            ->method('insert')
            ->with([
                'patient_id' => 12,
                'berat_badan' => 10.5,
                'tinggi_badan' => 82.1,
                'status_gizi' => 'baik',
                'keadaan_umum' => 'sehat',
                'tanggal_pemeriksaan' => '2026-05-03',
                'keterangan' => 'normal',
            ])
            ->willReturn(true);

        $growthRecordModel->expects($this->once())
            ->method('getInsertID')
            ->willReturn(99);

        $growthRecordModel->expects($this->once())
            ->method('find')
            ->with(99)
            ->willReturn([
                'id' => 99,
                'patient_id' => 12,
            ]);

        $queueModel->expects($this->once())
            ->method('find')
            ->with(7)
            ->willReturn([
                'id' => 7,
                'patient_id' => 12,
                'status' => 'served',
            ]);

        $queueModel->expects($this->once())
            ->method('update')
            ->with(7, ['status' => 'finished'])
            ->willReturn(true);

        $this->setPrivateProperty($service, 'growthRecordModel', $growthRecordModel);
        $this->setPrivateProperty($service, 'queueModel', $queueModel);

        $result = $service->create($payload);

        $this->assertSame([
            'id' => 99,
            'patient_id' => 12,
        ], $result);
    }

    public function testCreateReturnsNotFoundWhenQueueDoesNotExist(): void
    {
        $service = new GrowthRecordService();

        $growthRecordModel = $this->getMockBuilder(GrowthRecordModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insert'])
            ->getMock();

        $queueModel = $this->getMockBuilder(QueueModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();

        $growthRecordModel->expects($this->never())
            ->method('insert');

        $queueModel->expects($this->once())
            ->method('find')
            ->with(7)
            ->willReturn(null);

        $this->setPrivateProperty($service, 'growthRecordModel', $growthRecordModel);
        $this->setPrivateProperty($service, 'queueModel', $queueModel);

        $result = $service->create([
            'patient_id' => 12,
            'queue_id' => 7,
        ]);

        $this->assertSame([
            'error' => 'queue not found',
            'code' => 404,
        ], $result);
    }

    public function testCreateRollsBackInsertedGrowthRecordWhenQueueUpdateFails(): void
    {
        $service = new GrowthRecordService();

        $growthRecordModel = $this->getMockBuilder(GrowthRecordModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insert', 'getInsertID', 'delete'])
            ->getMock();

        $queueModel = $this->getMockBuilder(QueueModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find', 'update', 'errors'])
            ->getMock();

        $growthRecordModel->expects($this->once())
            ->method('insert')
            ->willReturn(true);

        $growthRecordModel->expects($this->once())
            ->method('getInsertID')
            ->willReturn(99);

        $growthRecordModel->expects($this->once())
            ->method('delete')
            ->with(99)
            ->willReturn(true);

        $queueModel->expects($this->once())
            ->method('find')
            ->with(7)
            ->willReturn([
                'id' => 7,
                'patient_id' => 12,
            ]);

        $queueModel->expects($this->once())
            ->method('update')
            ->with(7, ['status' => 'finished'])
            ->willReturn(false);

        $queueModel->expects($this->once())
            ->method('errors')
            ->willReturn([
                'status' => 'invalid status transition',
            ]);

        $this->setPrivateProperty($service, 'growthRecordModel', $growthRecordModel);
        $this->setPrivateProperty($service, 'queueModel', $queueModel);

        $result = $service->create([
            'patient_id' => 12,
            'queue_id' => 7,
        ]);

        $this->assertSame([
            'error' => [
                'status' => 'invalid status transition',
            ],
            'code' => 400,
        ], $result);
    }

    public function testGetByPatientUsesModelQuery(): void
    {
        $service = new GrowthRecordService();

        $growthRecordModel = $this->getMockBuilder(GrowthRecordModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByPatient'])
            ->getMock();

        $expected = [
            ['id' => 1, 'patient_id' => 12],
            ['id' => 2, 'patient_id' => 12],
        ];

        $growthRecordModel->expects($this->once())
            ->method('getByPatient')
            ->with(12)
            ->willReturn($expected);

        $this->setPrivateProperty($service, 'growthRecordModel', $growthRecordModel);

        $result = $service->getByPatient(12);

        $this->assertSame($expected, $result);
    }
}
