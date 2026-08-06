<?php

use App\Controllers\Api\V1\VisitServiceController;
use App\Models\VisitServiceModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;

/**
 * @internal
 */
final class VisitServiceControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpControllerTestTrait();
    }

    public function testIndexReturns200WithPaginationMeta(): void
    {
        $visitServiceModel = $this->getMockBuilder(VisitServiceModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPaginatedWithDetails'])
            ->getMock();

        $visitServiceModel->expects($this->once())
            ->method('getPaginatedWithDetails')
            ->with(10, 1)
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

        $visitServiceModel->pager = $pager;

        $result = $this->controller(VisitServiceController::class);
        $this->setPrivateProperty($this->controller, 'visitServiceModel', $visitServiceModel);

        $response = $result->execute('index');

        $this->assertSame(200, $response->response()->getStatusCode());
        $this->assertSame([
            'status' => 'success',
            'message' => 'Visit services data fetched',
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
