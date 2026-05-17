<?php

namespace App\Services;

use App\Models\QueueModel;
use App\Models\ServiceTypeModel;

class QueueService
{
    protected $queueModel;
    protected $serviceTypeModel;
    protected array $allowedFields = [
        'tanggal_kunjungan',
        'patient_id',
        'service_type_ids',
        'status',
    ];

    public function __construct()
    {
        $this->queueModel = new QueueModel();
        $this->serviceTypeModel = new ServiceTypeModel();
    }

    public function list(
        $perPage = 10,
        ?string $tanggal = null,
        ?string $status = null,
        ?string $nama = null,
        ?array $serviceTypeIds = null
    )
    {
        $queues = $this->queueModel->getQueueListWithPatient((int) $perPage, $tanggal, $status, $nama, $serviceTypeIds);

        return $this->enrichQueuesWithServices($queues);
    }

    public function find($id)
    {
        $queue = $this->queueModel->getQueueDetailWithPatient((int) $id);

        return $queue ? $this->enrichQueueWithServices($queue) : null;
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

        $serviceTypeValidation = $this->validateServiceTypeIds($payload['service_type_ids'] ?? null);
        if ($serviceTypeValidation !== true) {
            return [
                'error' => [
                    'service_type_ids' => $serviceTypeValidation,
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

        return $this->enrichQueueWithServices($inserted);
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

        if (array_key_exists('service_type_ids', $payload)) {
            $serviceTypeValidation = $this->validateServiceTypeIds($payload['service_type_ids']);
            if ($serviceTypeValidation !== true) {
                return [
                    'error' => [
                        'service_type_ids' => $serviceTypeValidation,
                    ],
                    'code' => 400,
                ];
            }
        }

        $updated = $this->queueModel->update($id, $payload);
        if ($updated === false) {
            return [
                'error' => $this->queueModel->errors(),
                'code' => 400,
            ];
        }

        return $this->find((int) $id);
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

        if (! array_key_exists('service_type_ids', $data)) {
            if (array_key_exists('layanan', $data)) {
                $data['service_type_ids'] = $data['layanan'];
            } elseif (array_key_exists('service_types', $data)) {
                $data['service_type_ids'] = $data['service_types'];
            }
        }

        $payload = array_intersect_key($data, array_flip($this->allowedFields));

        if (array_key_exists('patient_id', $payload)) {
            $payload['patient_id'] = $payload['patient_id'] === null || $payload['patient_id'] === ''
                ? null
                : (int) $payload['patient_id'];
        }

        if (array_key_exists('service_type_ids', $payload)) {
            $payload['service_type_ids'] = $this->normalizeServiceTypeIds($payload['service_type_ids']);
        }

        if (array_key_exists('tanggal_kunjungan', $payload) && is_string($payload['tanggal_kunjungan'])) {
            $payload['tanggal_kunjungan'] = trim($payload['tanggal_kunjungan']);
        }

        if (array_key_exists('status', $payload) && is_string($payload['status'])) {
            $payload['status'] = trim($payload['status']);
        }

        return $payload;
    }

    private function normalizeServiceTypeIds($serviceTypeIds): ?string
    {
        if (is_array($serviceTypeIds)) {
            $serviceTypeIds = implode(',', $serviceTypeIds);
        }

        if (! is_string($serviceTypeIds)) {
            return null;
        }

        $parts = array_filter(array_map('trim', explode(',', $serviceTypeIds)), static fn ($value) => $value !== '');
        if ($parts === []) {
            return null;
        }

        $normalized = [];

        foreach ($parts as $part) {
            if (! ctype_digit($part)) {
                return null;
            }

            $normalized[] = (string) ((int) $part);
        }

        return implode(',', array_values(array_unique($normalized)));
    }

    private function validateServiceTypeIds(?string $serviceTypeIds)
    {
        if ($serviceTypeIds === null || $serviceTypeIds === '') {
            return 'service_type_ids is required and must contain comma-separated numeric IDs';
        }

        $serviceTypeIdsArray = array_map('intval', explode(',', $serviceTypeIds));
        $serviceTypes = $this->serviceTypeModel
            ->whereIn('id', $serviceTypeIdsArray)
            ->findAll();

        if (count($serviceTypes) !== count(array_unique($serviceTypeIdsArray))) {
            return 'one or more service types were not found';
        }

        return true;
    }

    private function enrichQueuesWithServices(array $queues): array
    {
        if ($queues === []) {
            return [];
        }

        $serviceTypeIds = [];

        foreach ($queues as $queue) {
            $serviceTypeIds = array_merge($serviceTypeIds, $this->extractServiceTypeIds($queue['service_type_ids'] ?? null));
        }

        $serviceTypesById = $this->getServiceTypesById($serviceTypeIds);

        return array_map(fn (array $queue) => $this->attachServiceTypes($queue, $serviceTypesById), $queues);
    }

    private function enrichQueueWithServices(array $queue): array
    {
        $serviceTypesById = $this->getServiceTypesById($this->extractServiceTypeIds($queue['service_type_ids'] ?? null));

        return $this->attachServiceTypes($queue, $serviceTypesById);
    }

    private function extractServiceTypeIds(?string $serviceTypeIds): array
    {
        if ($serviceTypeIds === null || $serviceTypeIds === '') {
            return [];
        }

        return array_map('intval', explode(',', $serviceTypeIds));
    }

    private function getServiceTypesById(array $serviceTypeIds): array
    {
        $serviceTypeIds = array_values(array_unique(array_filter($serviceTypeIds)));
        if ($serviceTypeIds === []) {
            return [];
        }

        $serviceTypes = $this->serviceTypeModel
            ->whereIn('id', $serviceTypeIds)
            ->findAll();

        $mapped = [];
        foreach ($serviceTypes as $serviceType) {
            $mapped[(int) $serviceType['id']] = $serviceType;
        }

        return $mapped;
    }

    private function attachServiceTypes(array $queue, array $serviceTypesById): array
    {
        $serviceTypeIds = $this->extractServiceTypeIds($queue['service_type_ids'] ?? null);
        $serviceTypes = [];
        $serviceNames = [];

        foreach ($serviceTypeIds as $serviceTypeId) {
            if (! isset($serviceTypesById[$serviceTypeId])) {
                continue;
            }

            $serviceTypes[] = $serviceTypesById[$serviceTypeId];
            $serviceNames[] = $serviceTypesById[$serviceTypeId]['nama_layanan'] ?? null;
        }

        $queue['service_type_ids'] = implode(',', $serviceTypeIds);
        $queue['service_type_id_list'] = $serviceTypeIds;
        $queue['service_types'] = $serviceTypes;
        $queue['service_type_names'] = array_values(array_filter($serviceNames, static fn ($value) => $value !== null && $value !== ''));

        return $queue;
    }
}
