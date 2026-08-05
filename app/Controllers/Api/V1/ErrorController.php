<?php

namespace App\Controllers\Api\V1;


use CodeIgniter\RESTful\ResourceController;

class ErrorController extends ResourceController
{
    public function notFound()
    {
        return $this->respond([
            'status'  => 'Error',
            'message' => 'Endpoint not found',
            'data'    => null,
            'errors'  => null
        ], 404);
    }
}
