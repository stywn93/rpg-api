<?php

namespace App\Controllers\Api\V1;

use App\Services\UserService;
use CodeIgniter\RESTful\ResourceController;

class UserController extends ResourceController
{
    protected $format = 'json';
    protected $userService;

    public function __construct(){
        $this->userService = new UserService();
    }

    public function index()
    {
        $perPage = $this->request->getGet('per_page') ?? 10;
        return $this->respond([
            'status' => 'success',
            'message' => 'Users data fetched',
            'data' => $this->userService->list($perPage),
            'errors' => null
        ]);
    }

    public function show($id = null){
        $user = $this->userService->find($id);
        if(!$user){
            return $this->failNotFound("User not found");
        }
        return $this->respond([
            'status' => 'success',
            'message' => 'User data fetched',
            'data' => $user,
            'errors' => null
        ]);
    }

    public function create(){
        $data = $this->request->getJSON(true);
        $insert = $this->userService->create($data);
        if (isset($insert['error'])) {
            return $this->failValidationErrors($insert['error']);
        }
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $insert,
            'errors' => null
        ]);
    }

    public function update($id = null){
        $data = $this->request->getJSON(true);
        $user = $this->userService->update($id, $data);

        if (isset($user['error'])) {
            if (($user['code'] ?? 500) === 404) {
                return $this->failNotFound($user['error']);
            }

            if (($user['code'] ?? 500) === 409) {
                return $this->failValidationErrors($user['error']);
            }

            if (($user['code'] ?? 500) === 400) {
                return $this->failValidationErrors($user['error']);
            }
        }
        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user,
            'errors' => null
        ]);

    }

    public function delete($id = null){
        $user = $this->userService->delete($id);
        if (isset($user['error'])) {
            if (($user['code'] ?? 500) === 404) {
                return $this->failNotFound($user['error']);
            }
        }
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'User deleted successfully',
            'data' => $id,
            'errors' => null
        ]);
    }


    public function activate($id){ 
        $result = $this->userService->activate($id);

        if (isset($result['error'])) {
            if (($result['code'] ?? 500) === 404) {
                return $this->failNotFound($result['error']);
            }

            if (($result['code'] ?? 500) === 422) {
                return $this->failValidationErrors($result['error']);
            }

            return $this->fail($result['error'], $result['code'] ?? 400);
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'User '. $id .' activated',
            'data' => $id ?? null,
            'errors' => null
        ]);
    }

    public function suspend($id){
        $result = $this->userService->suspend($id);

        if (isset($result['error'])) {
            if (($result['code'] ?? 500) === 404) {
                return $this->failNotFound($result['error']);
            }

            if (($result['code'] ?? 500) === 422) {
                return $this->failValidationErrors($result['error']);
            }

            return $this->fail($result['error'], $result['code'] ?? 400);
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'User '. $id .' suspended',
            'data' => $id ?? null,
            'errors' => null
        ]);
    }
}
