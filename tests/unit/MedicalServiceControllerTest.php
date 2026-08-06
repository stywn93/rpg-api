<?php

use App\Controllers\Api\V1\MedicalServiceController;
use App\Models\MedicalServiceModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;

/**
 * @internal
 */
final class MedicalServiceControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpControllerTestTrait();
    }

    public function testIndexReturns200WithPaginationMeta(): void
    {
        $medicalServiceModel = $this->getMockBuilder(MedicalServiceModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['paginate'])
            ->getMock();

        $medicalServiceModel->expects($this->once())
            ->method('paginate')
            ->with(10, 'default', 1)
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

        $medicalServiceModel->pager = $pager;

        $result = $this->controller(MedicalServiceController::class);
        $this->setPrivateProperty($this->controller, 'medicalServiceModel', $medicalServiceModel);

        $response = $result->execute('index');

        $this->assertSame(200, $response->response()->getStatusCode());
        $this->assertSame([
            'status' => 'success',
            'message' => 'Medical services data fetched',
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
