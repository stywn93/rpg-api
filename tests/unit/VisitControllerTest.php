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

    protected function tearDown(): void
    {
        service('superglobals')->setGetArray([]);
        parent::tearDown();
    }

    public function testIndexForwardsFiltersToModel(): void
    {
        service('superglobals')->setGetArray([
            'patient_id'   => '5',
            'visit_date'   => '2026-08-01',
            'patient_name' => 'Budi',
            'visit_status' => 'waiting',
        ]);

        $calls = [];

        $visitModel = $this->getMockBuilder(VisitModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPaginatedWithPatient', '__call'])
            ->getMock();

        $visitModel->expects($this->exactly(4))
            ->method('__call')
            ->willReturnCallback(function (string $name, array $params) use ($visitModel, &$calls) {
                $calls[] = [$name, $params];

                return $visitModel;
            });

        $visitModel->expects($this->once())
            ->method('getPaginatedWithPatient')
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

        $visitModel->pager = $pager;

        $result = $this->controller(VisitController::class);
        $this->setPrivateProperty($this->controller, 'visitModel', $visitModel);

        $response = $result->execute('index');

        $this->assertSame(200, $response->response()->getStatusCode());
        $this->assertSame([
            ['where', ['patient_id', '5']],
            ['where', ['visit_date', '2026-08-01']],
            ['like', ['patients.name', 'Budi']],
            ['where', ['visit_status', 'waiting']],
        ], $calls);
    }

    public function testIndexByParentRequiresParentId(): void
    {
        service('superglobals')->setGetArray([]);

        $visitModel = $this->getMockBuilder(VisitModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->controller(VisitController::class);
        $this->setPrivateProperty($this->controller, 'visitModel', $visitModel);

        $response = $result->execute('indexByParent');

        $this->assertSame(400, $response->response()->getStatusCode());
        $this->assertSame([
            'status' => 'failed',
            'message' => 'parent_id is required',
            'data' => null,
            'errors' => '400',
        ], json_decode($response->getJSON(), true));
    }

    public function testIndexByParentForwardsParentIdAndFiltersToModel(): void
    {
        service('superglobals')->setGetArray([
            'parent_id'    => '3',
            'patient_id'   => '5',
            'visit_date'   => '2026-08-01',
            'patient_name' => 'Budi',
            'visit_status' => 'waiting',
        ]);

        $calls = [];

        $visitModel = $this->getMockBuilder(VisitModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPaginatedByParentWithPatient', '__call'])
            ->getMock();

        $visitModel->expects($this->exactly(4))
            ->method('__call')
            ->willReturnCallback(function (string $name, array $params) use ($visitModel, &$calls) {
                $calls[] = [$name, $params];

                return $visitModel;
            });

        $visitModel->expects($this->once())
            ->method('getPaginatedByParentWithPatient')
            ->with(3, 10, 1)
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

        $response = $result->execute('indexByParent');

        $this->assertSame(200, $response->response()->getStatusCode());
        $this->assertSame([
            ['where', ['patient_id', '5']],
            ['where', ['visit_date', '2026-08-01']],
            ['like', ['patients.name', 'Budi']],
            ['where', ['visit_status', 'waiting']],
        ], $calls);
    }

    public function testIndexReturns200WithPaginationMeta(): void
    {
        $visitModel = $this->getMockBuilder(VisitModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPaginatedWithPatient'])
            ->getMock();

        $visitModel->expects($this->once())
            ->method('getPaginatedWithPatient')
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
