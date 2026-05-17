<?php

use App\Controllers\Api\V1\PatientController;
use App\Services\PatientService;
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
        $patientService = $this->getMockBuilder(PatientService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByParent'])
            ->getMock();

        $patientService->expects($this->once())
            ->method('getByParent')
            ->with('99')
            ->willReturn([]);

        $result = $this->controller(PatientController::class);
        $this->setPrivateProperty($this->controller, 'patientService', $patientService);

        $response = $result->execute('showByParent', '99');

        $this->assertSame(200, $response->response()->getStatusCode());
        $this->assertSame([
            'status' => 'success',
            'message' => 'patient data fetched',
            'data' => [],
            'errors' => null,
        ], json_decode($response->getJSON(), true));
    }
}
