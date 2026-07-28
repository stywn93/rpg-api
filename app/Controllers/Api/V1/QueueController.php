<?php

namespace App\Controllers\Api\V1;


use App\Services\QueueService;
use CodeIgniter\RESTful\ResourceController;
use DateTime;

class QueueController extends ResourceController
{
    protected $format = 'json';
    protected $queueService;

    public function __construct()
    {
        $this->queueService = new QueueService();
    }

    public function index()
    {
        $perPage = max(1, (int) ($this->request->getGet('per_page') ?? 10));
        $tanggal = $this->request->getGet('tanggal_kunjungan') ?? $this->request->getGet('tanggal');
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $serviceTypeFilter = $this->parseServiceTypeFilter(
            $this->request->getGet('jenis_layanan')
            ?? $this->request->getGet('service_type_ids')
            ?? $this->request->getGet('layanan')
        );
        $nama = trim((string) (
            $this->request->getGet('nama')
            ?? $this->request->getGet('search')
            ?? $this->request->getGet('searchTerm')
            ?? ''
        ));

        if ($tanggal !== null && ! $this->isValidDate($tanggal)) {
            return $this->failValidationErrors([
                'tanggal_kunjungan' => 'tanggal_kunjungan must use format Y-m-d',
            ]);
        }

        if (! $serviceTypeFilter['valid']) {
            return $this->failValidationErrors([
                'jenis_layanan' => 'jenis_layanan must contain numeric IDs, separated by commas or repeated parameters',
            ]);
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'queues data fetched',
            'data' => $this->queueService->list($perPage, $tanggal, $status, $nama, $serviceTypeFilter['values']),
            'errors' => null
        ]);
    }

    public function filterByDate($tanggal)
    {
        $perPage = max(1, (int) ($this->request->getGet('per_page') ?? 10));
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $serviceTypeFilter = $this->parseServiceTypeFilter(
            $this->request->getGet('jenis_layanan')
            ?? $this->request->getGet('service_type_ids')
            ?? $this->request->getGet('layanan')
        );
        $nama = trim((string) (
            $this->request->getGet('nama')
            ?? $this->request->getGet('search')
            ?? $this->request->getGet('searchTerm')
            ?? ''
        ));

        if (! $this->isValidDate($tanggal)) {
            return $this->failValidationErrors([
                'tanggal_kunjungan' => 'tanggal_kunjungan must use format Y-m-d',
            ]);
        }

        if (! $serviceTypeFilter['valid']) {
            return $this->failValidationErrors([
                'jenis_layanan' => 'jenis_layanan must contain numeric IDs, separated by commas or repeated parameters',
            ]);
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'queues data fetched',
            'data' => $this->queueService->list($perPage, $tanggal, $status, $nama, $serviceTypeFilter['values']),
            'errors' => null
        ]);
    }

    public function show($id = null)
    {
        $queue = $this->queueService->find($id);
        if (!$queue) {
            return $this->failNotFound("queue not founds");
        }
        return $this->respond([
            'status' => 'success',
            'message' => 'queue data fetched',
            'data' => $queue,
            'errors' => null
        ]);
    }

    public function create()
    {
        $data = $this->getRequestData();
        $insert = $this->queueService->create($data);
        if (isset($insert['error'])) {
            if (($insert['code'] ?? 500) === 400) {
                return $this->failValidationErrors($insert['error']);
            }

            return $this->failValidationErrors($insert['error']);
        }

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'queue created successfully',
            'data' => $insert,
            'errors' => null
        ]);
    }

    public function update($id = null)
    {
        $data = $this->getRequestData();
        $queue = $this->queueService->update($id, $data);

        if (isset($queue['error'])) {
            if (($queue['code'] ?? 500) === 404) {
                return $this->failNotFound($queue['error']);
            }

            if (($queue['code'] ?? 500) === 400) {
                return $this->failValidationErrors($queue['error']);
            }
        }

        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'queue updated successfully',
            'data' => $queue,
            'errors' => null
        ]);

    }

    public function delete($id = null)
    {
        $queue = $this->queueService->delete($id);
        if (isset($queue['error'])) {
            if (($queue['code'] ?? 500) === 404) {
                return $this->failNotFound($queue['error']);
            }
        }
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'queue deleted successfully',
            'data' => $id,
            'errors' => null
        ]);
    }

    private function getRequestData(): array
    {
        $json = $this->request->getJSON(true);
        if (is_array($json)) {
            return $json;
        }

        $raw = $this->request->getRawInput();
        if (is_array($raw) && $raw !== []) {
            return $raw;
        }

        $post = $this->request->getPost();
        return is_array($post) ? $post : [];
    }

    private function isValidDate(string $tanggal): bool
    {
        $date = DateTime::createFromFormat('Y-m-d', $tanggal);

        return $date !== false && $date->format('Y-m-d') === $tanggal;
    }

    private function parseServiceTypeFilter($value): array
    {
        if ($value === null) {
            return [
                'valid' => true,
                'values' => null,
            ];
        }

        $parts = is_array($value) ? $value : explode(',', (string) $value);
        $normalized = [];

        foreach ($parts as $part) {
            $part = trim((string) $part);

            if ($part === '') {
                continue;
            }

            if (! ctype_digit($part)) {
                return [
                    'valid' => false,
                    'values' => null,
                ];
            }

            $normalized[] = (int) $part;
        }

        $normalized = array_values(array_unique($normalized));

        return [
            'valid' => true,
            'values' => $normalized === [] ? null : $normalized,
        ];
    }

    // new query builder for v_queues view
    public function listWithPatients(
        int $perPage = 10,
        ?string $tanggal = null,
        ?string $status = null,
        ?string $nama = null,
        ?array $serviceTypeIds = null
    ) {
        return $this->respond([
            'status' => 'success',
            'message' => 'queues with patients data fetched',
            'data' => $this->queueService->listFromView($perPage, $tanggal, $status, $nama, $serviceTypeIds),
            'errors' => null
        ]);
    }
}
