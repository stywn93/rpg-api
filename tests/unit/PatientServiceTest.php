<?php

use App\Models\PatientModel;
use App\Services\PatientService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PatientServiceTest extends CIUnitTestCase
{
    public function testListUsesPaginatedPatientsWithAge(): void
    {
        $service = new PatientService();

        $patientModel = $this->getMockBuilder(PatientModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPaginatedWithAge'])
            ->getMock();

        $patientModel->expects($this->once())
            ->method('getPaginatedWithAge')
            ->with(15)
            ->willReturn([['id' => 1, 'usia' => '3 tahun 2 bulan']]);

        $this->setPrivateProperty($service, 'patientModel', $patientModel);

        $result = $service->list(15);

        $this->assertSame([['id' => 1, 'usia' => '3 tahun 2 bulan']], $result);
    }

    public function testFindReturnsPatientWithAge(): void
    {
        $service = new PatientService();

        $patientModel = $this->getMockBuilder(PatientModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findWithAge'])
            ->getMock();

        $patientModel->expects($this->once())
            ->method('findWithAge')
            ->with(5)
            ->willReturn(['id' => 5, 'usia' => '2 tahun 4 bulan']);

        $this->setPrivateProperty($service, 'patientModel', $patientModel);

        $result = $service->find(5);

        $this->assertSame(['id' => 5, 'usia' => '2 tahun 4 bulan'], $result);
    }

    public function testCreateReturnsInsertedPatientWithAge(): void
    {
        $service = new PatientService();

        $patientModel = $this->getMockBuilder(PatientModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insert', 'getInsertID', 'findWithAge'])
            ->getMock();

        $patientModel->expects($this->once())
            ->method('insert')
            ->with([
                'parent_id' => 3,
                'no_kk' => '1234567890123456',
                'nama' => 'Budi',
                'tanggal_lahir' => '2023-01-15',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Melati',
            ])
            ->willReturn(true);

        $patientModel->expects($this->once())
            ->method('getInsertID')
            ->willReturn(11);

        $patientModel->expects($this->once())
            ->method('findWithAge')
            ->with(11)
            ->willReturn(['id' => 11, 'usia' => '3 tahun 3 bulan']);

        $this->setPrivateProperty($service, 'patientModel', $patientModel);

        $result = $service->create([
            'parent_id' => '3',
            'no_kk' => '1234567890123456',
            'nama' => ' Budi ',
            'tanggal_lahir' => '2023-01-15',
            'jenis_kelamin' => 'L',
            'alamat' => ' Jl. Melati ',
        ]);

        $this->assertSame(['id' => 11, 'usia' => '3 tahun 3 bulan'], $result);
    }

    public function testGetByParentReturnsPatientsWithAge(): void
    {
        $service = new PatientService();

        $patientModel = $this->getMockBuilder(PatientModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByParentWithAge'])
            ->getMock();

        $patientModel->expects($this->once())
            ->method('getByParentWithAge')
            ->with(9, 10)
            ->willReturn([['id' => 21, 'usia' => '1 tahun 8 bulan']]);

        $this->setPrivateProperty($service, 'patientModel', $patientModel);

        $result = $service->getByParent(9);

        $this->assertSame([['id' => 21, 'usia' => '1 tahun 8 bulan']], $result);
    }
}
