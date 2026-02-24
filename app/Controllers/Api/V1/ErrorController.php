<?php

namespace App\Controllers\Api\V1;

use App\Services\UserService;
use CodeIgniter\RESTful\ResourceController;

class ErrorController extends ResourceController
{
    public function notFound()
    {
        return $this->respond([
            'status'  => 'Error',
            'message' => 'Endpoint tidak ditemukan',
            'data'    => null,
            'errors'  => null
        ], 404);
    }
}
