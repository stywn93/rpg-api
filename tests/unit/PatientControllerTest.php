<?php

use App\Controllers\Api\V1\PatientController;
use App\Models\PatientModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;

/**
 * @internal
 */
final class PatientControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpControllerTestTrait();
    }

    public function testShowByParentReturns200WithEmptyDataWhenNoPatientsFound(): void
    {
        $patientModel = $this->getMockBuilder(PatientModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByParentWithAge'])
            ->getMock();

        $patientModel->expects($this->once())
            ->method('getByParentWithAge')
            ->with(99, 10, 1)
            ->willReturn([]);

        $pager = new class {
            public function getTotal(): int
            {
                return 0;
            }

            public function getPerPage(): int
            {
                return 10;
            }

            public function getCurrentPage(): int
            {
                return 1;
            }

            public function getPageCount(): int
            {
                return 1;
            }
        };

        $patientModel->pager = $pager;

        $result = $this->controller(PatientController::class);
        $this->setPrivateProperty($this->controller, 'patientModel', $patientModel);

        $response = $result->execute('showByParent', '99');

        $this->assertSame(200, $response->response()->getStatusCode());
        $this->assertSame([
            'status' => 'success',
            'message' => 'Patients data fetched',
            'data' => [],
            'meta' => [
                'total' => 0,
                'per_page' => 10,
                'current_page' => 1,
                'last_page' => 1,
            ],
        ], json_decode($response->getJSON(), true));
    }
}
