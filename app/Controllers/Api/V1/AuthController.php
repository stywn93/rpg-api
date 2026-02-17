<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Services\AuthService;


class AuthController extends ResourceController
{
    public function __construct(){
        $this->authService = new AuthService();
    }
    public function register()
    {
        $data = $this->request->getJSON(true);

        $result = $this->authService->register($data);

        if (isset($result['error'])) {
            return $this->fail($result['error'], 400);
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => $data
        ]);
    }

    public function login()
    {
        $data = $this->request->getJSON(true);

        $result = $this->authService->login(
            $data['email'],
            $data['password']
        );

        if (isset($result['error'])) {
            return $this->failUnauthorized($result['error']);
        }

        return $this->respond([
            'status' => 'success',
            'data' => $result
        ]);
    }
}
