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
            'message' => 'User data fetched successfully',
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
            'message' => 'User data fetched successfully',
            'data' => $user,
            'errors' => null
        ]);
    }

    public function create(){
        $data = $this->request->getJSON(true);
        $user = $this->userService->create($data);
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $data,
            'errors' => null
        ]);
    }

    public function update($id = null){
        $data = $this->request->getJSON(true);
        $user = $this->userService->update($id, $data);
        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $data,
            'errors' => null
        ]);
    }

    public function delete($id = null){
        $this->userService->delete($id);
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'User deleted successfully',
            'data' => $id,
            'errors' => null
        ]);
    }

    public function activate($id){
        $this->userService->activate($id);
        return $this->respond([
            'status' => 'success',
            'message' => 'User '. $id .' activated successfully',
            'data' => $id,
            'errors' => null
        ]);
    }

    public function suspend($id){
        $this->userService->suspend($id);
        return $this->respond([
            'status' => 'success',
            'message' => 'User '.$id.' has been suspended',
            'data' => $id,
            'errors' => null
        ]);
    }
}
