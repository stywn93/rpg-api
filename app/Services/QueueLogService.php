<?php

namespace App\Services;

use App\Models\QueueLogModel;


class QueueLogService
{
    protected $queueLogModel;


    public function __construct()
    {
        $this->queueLogModel = new QueueLogModel();

    }

    public function list($perPage = 10)
    {
        return $this->queueLogModel->paginate($perPage);
    }

    public function find($id)
    {
        return $this->queueLogModel->find($id);
    }

    public function create($data)
    {
        $inserted = $this->queueLogModel->insert($data);
        if ($inserted === false) {
            return ['error' => $this->queueLogModel->errors()];
        }
        return $this->queueLogModel->find($this->queueLogModel->getInsertID());
    }

    public function update($id, $data)
    {
        $queueLog = $this->queueLogModel->find($id);

        if (!$queueLog) {
            return [
                'error' => 'queue log not found',
                'code' => 404
            ];
        }
        $this->queueLogModel->update($id, $data);
        return $this->queueLogModel->find($id);
    }


    public function delete($id)
    {
        $queueLog = $this->queueLogModel->find($id);

        if (!$queueLog) {
            return [
                'error' => 'queue log not found',
                'code' => 404
            ];
        }
        return $this->queueLogModel->delete($id);
    }

}
