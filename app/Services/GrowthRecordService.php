<?php

namespace App\Services;

use App\Models\GrowthRecordModel;
use App\Models\QueueModel;

class GrowthRecordService
{
    protected $growthRecordModel;
    protected $queueModel;

    public function __construct()
    {
        $this->growthRecordModel = new growthRecordModel();
        $this->queueModel = new QueueModel();
    }

    public function list($perPage = 10)
    {
        return $this->growthRecordModel->paginate($perPage);
    }

    public function find($id)
    {
        return $this->growthRecordModel->find($id);
    }

    public function getByPatient($patientId)
    {
        return $this->growthRecordModel->getByPatient($patientId);
    }

    public function create($data)
    {
        if (! is_array($data)) {
            return ['error' => ['payload' => 'growth record payload is empty or invalid'], 'code' => 400];
        }

        $queueId = $this->extractQueueId($data);
        unset($data['queue_id']);

        if ($queueId !== null) {
            $queue = $this->queueModel->find($queueId);

            if (! $queue) {
                return ['error' => 'queue not found', 'code' => 404];
            }

            if (isset($data['patient_id']) && (int) $queue['patient_id'] !== (int) $data['patient_id']) {
                return ['error' => ['queue_id' => 'queue does not belong to the provided patient'], 'code' => 400];
            }
        }

        $inserted = $this->growthRecordModel->insert($data);
        if ($inserted === false) {
            return ['error' => $this->growthRecordModel->errors(), 'code' => 400];
        }

        $insertId = $this->growthRecordModel->getInsertID();

        if ($queueId !== null) {
            $updated = $this->queueModel->update($queueId, ['status' => 'finished']);

            if ($updated === false) {
                $this->growthRecordModel->delete($insertId);

                return ['error' => $this->queueModel->errors(), 'code' => 400];
            }
        }

        return $this->growthRecordModel->find($insertId);
    }

    public function update($id, $data)
    {
        $growthRecord = $this->growthRecordModel->find($id);

        if (!$growthRecord) {
            return [
                'error' => 'growthRecord not found',
                'code' => 404
            ];
        }

        $updated = $this->growthRecordModel->update($id, $data);
        if ($updated === false) {
            return ['error' => $this->growthRecordModel->errors(), 'code' => 400];
        }

        return $this->growthRecordModel->find($id);
    }


    public function delete($id)
    {
        $growthRecord = $this->growthRecordModel->find($id);

        if (!$growthRecord) {
            return [
                'error' => 'growth record not found',
                'code' => 404
            ];
        }
        return $this->growthRecordModel->delete($id);
    }

    private function extractQueueId(array $data): ?int
    {
        if (! array_key_exists('queue_id', $data) || $data['queue_id'] === null || $data['queue_id'] === '') {
            return null;
        }

        return (int) $data['queue_id'];
    }

}
