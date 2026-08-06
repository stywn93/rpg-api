<?php

use App\Controllers\Api\V1\VisitController;
use App\Models\VisitModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;

/**
 * @internal
 */
final class VisitControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpControllerTestTrait();
    }

    public function testIndexReturns200WithPaginationMeta(): void
    {
        $visitModel = $this->getMockBuilder(VisitModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['paginate'])
            ->getMock();

        $visitModel->expects($this->once())
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

        $visitModel->pager = $pager;

        $result = $this->controller(VisitController::class);
        $this->setPrivateProperty($this->controller, 'visitModel', $visitModel);

        $response = $result->execute('index');

        $this->assertSame(200, $response->response()->getStatusCode());
        $this->assertSame([
            'status' => 'success',
            'message' => 'Visits data fetched',
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
