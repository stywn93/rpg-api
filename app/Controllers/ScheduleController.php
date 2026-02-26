<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\scheduleService;
use CodeIgniter\HTTP\ResponseInterface;

class ScheduleController extends BaseController
{
    protected $format = 'json';
    protected $scheduleService;

    public function __construct()
    {
        $this->scheduleService = new scheduleService();
    }

    public function index()
    {
        $perPage = $this->request->getGet('per_page') ?? 10;
        return $this->respond([
            'status' => 'success',
            'message' => 'schedules data fetched',
            'data' => $this->scheduleService->list($perPage),
            'errors' => null
        ]);
    }

    public function show($id = null)
    {
        $schedule = $this->scheduleService->find($id);
        if (!$schedule) {
            return $this->failNotFound("schedule not founds");
        }
        return $this->respond([
            'status' => 'success',
            'message' => 'schedule data fetched',
            'data' => $schedule,
            'errors' => null
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $insert = $this->scheduleService->create($data);
        if (isset($insert['error'])) {
            return $this->failValidationErrors($insert['error']);
        }
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'schedule created successfully',
            'data' => $insert,
            'errors' => null
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $schedule = $this->scheduleService->update($id, $data);

        if (isset($schedule['error'])) {
            if (($schedule['code'] ?? 500) === 404) {
                return $this->failNotFound($schedule['error']);
            }

            if (($schedule['code'] ?? 500) === 409) {
                return $this->failValidationErrors($schedule['error']);
            }

            if (($schedule['code'] ?? 500) === 400) {
                return $this->failValidationErrors($schedule['error']);
            }
        }
        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'schedule updated successfully',
            'data' => $schedule,
            'errors' => null
        ]);

    }

    public function delete($id = null)
    {
        $schedule = $this->scheduleService->delete($id);
        if (isset($schedule['error'])) {
            if (($schedule['code'] ?? 500) === 404) {
                return $this->failNotFound($schedule['error']);
            }
        }
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'schedule deleted successfully',
            'data' => $id,
            'errors' => null
        ]);
    }

    public function showByParent($parentID = null)
    {
        $schedules = $this->scheduleService->getByParent($parentID);
        if (!$schedules) {
            return $this->failNotFound("schedule not found");
        }
        return $this->respond([
            'status' => 'success',
            'message' => 'schedule data fetched',
            'data' => $schedules,
            'errors' => null
        ]);
    }
}
