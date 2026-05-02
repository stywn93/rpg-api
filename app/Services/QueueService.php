<?php

namespace App\Services;

use App\Models\QueueModel;

class QueueService
{
    protected $queueModel;
    protected array $allowedFields = [
        'tanggal_kunjungan',
        'patient_id',
        'status',
    ];

    public function __construct()
    {
        $this->queueModel = new QueueModel();
    }

    public function list($perPage = 10, ?string $tanggal = null)
    {
        return $this->queueModel->getQueueListWithPatient((int) $perPage, $tanggal);
    }

    public function find($id)
    {
        return $this->queueModel->find($id);
    }

    public function create($data)
    {
        $payload = $this->preparePayload($data);

        if ($payload === []) {
            return [
                'error' => [
                    'payload' => 'queue payload is empty or invalid',
                ],
                'code' => 400,
            ];
        }

        $inserted = $this->queueModel->insertWithDailyQueueNumber($payload);
        if ($inserted === false) {
            return [
                'error' => $this->queueModel->errors(),
                'code' => 400,
            ];
        }

        return $inserted;
    }

    public function update($id, $data)
    {
        $queue = $this->queueModel->find($id);

        if (!$queue) {
            return [
                'error' => 'queue not found',
                'code' => 404
            ];
        }

        $payload = $this->preparePayload($data);
        if ($payload === []) {
            return [
                'error' => [
                    'payload' => 'queue payload is empty or invalid',
                ],
                'code' => 400,
            ];
        }

        $updated = $this->queueModel->update($id, $payload);
        if ($updated === false) {
            return [
                'error' => $this->queueModel->errors(),
                'code' => 400,
            ];
        }

        return $this->queueModel->find($id);
    }

    public function delete($id)
    {
        $queue = $this->queueModel->find($id);

        if (!$queue) {
            return [
                'error' => 'queue not found',
                'code' => 404
            ];
        }
        return $this->queueModel->delete($id);
    }

    private function preparePayload($data): array
    {
        if (! is_array($data)) {
            return [];
        }

        $payload = array_intersect_key($data, array_flip($this->allowedFields));

        if (array_key_exists('patient_id', $payload)) {
            $payload['patient_id'] = $payload['patient_id'] === null || $payload['patient_id'] === ''
                ? null
                : (int) $payload['patient_id'];
        }

        if (array_key_exists('tanggal_kunjungan', $payload) && is_string($payload['tanggal_kunjungan'])) {
            $payload['tanggal_kunjungan'] = trim($payload['tanggal_kunjungan']);
        }

        if (array_key_exists('status', $payload) && is_string($payload['status'])) {
            $payload['status'] = trim($payload['status']);
        }

        return $payload;
    }
}
