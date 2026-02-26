<?php

namespace App\Services;

use App\Models\QueueModel;
use App\Models\ServiceTypeModel;

class QueueService
{
    protected $queueModel;


    public function __construct()
    {
        $this->queueModel = new QueueModel();

    }

    public function list($perPage = 10)
    {
        return $this->queueModel->paginate($perPage);
    }

    public function find($id)
    {
        return $this->queueModel->find($id);
    }

    public function create($data)
    {
        $inserted = $this->queueModel->insert($data);
        if ($inserted === false) {
            return ['error' => $this->queueModel->errors()];
        }
        return $this->queueModel->find($this->queueModel->getInsertID());
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
        $this->queueModel->update($id, $data);
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

}
