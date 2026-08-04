<?php

namespace App\Controllers\Api\V1;

use App\Services\UserService;
use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class UserController extends ResourceController
{
    protected $format = 'json';
    protected $userService;
    protected $userModel;

    public function __construct(){
        $this->userService = new UserService();
        $this->userModel = new UserModel();
    }

    function index(){
        $page   = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? 3;
        
        //search parameter
        $status = trim((string) $this->request->getGet('status') ?? '');
        $role   = trim((string) ($this->request->getGet('role') ?? ''));
        $email  = trim((string) $this->request->getGet('email') ?? '');
        $address = trim((string) $this->request->getGet('address') ?? '');
        $name   = trim((string) $this->request->getGet('name') ?? '');

        if ($name !== '') {
        $this->userModel->groupStart()
              ->like('name', $name)
              ->groupEnd();
        }
        // Filter role (exact match, AND dengan yang lain)
        if ($role !== '') {
            $this->userModel->where('role', $role);
        }
        if ($status !== '') {
            $this->userModel->where('status', $status);
        }
        if($address !== ''){
            $this->userModel->like('address', $address);
        }

        $users  = $this->userModel->paginate($perPage, 'default', $page);
        $pager  = $this->userModel->pager;

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Users data fetched',
            'data' => $users,
            'meta'    => [
                'total'        => $pager->getTotal(),
                'per_page'     => $pager->getPerPage(),
                'current_page' => $pager->getCurrentPage(),
                'last_page'    => $pager->getPageCount(),
            ],
        ]);
    }

    public function show($id = null){
        $user = $this->userModel->find($id);
        if(!$user){
            return $this->failNotFound("User not found");
        }
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'User data fetched',
            'data' => $user,
            'errors' => null
        ]);
    }


    public function create(){
        $data = $this->request->getJSON(true);
        
        // $data = $this->normalizeRolePayload($data);
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        if ($this->userModel->withDeleted()->where('email', $data['email'])->first()) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Email already used',
                'data' => $data['email'],
                'errors' => '409'
            ]);
        }
        $inserted = $this->userModel->insert($data);
        if ($inserted === false) {
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'Error creating user',
                'data' => $data,
                'errors' => $this->userModel->errors()
            ]);
        }
        
        //if success then return this
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'User created',
            'data' => $this->userModel->find($this->userModel->getInsertID()),
            'errors' => '-'
        ]);
        // return $this->presentUser($this->userModel->find($this->userModel->getInsertID()));
    }

    public function update($id = null){
        $data = $this->request->getJSON(true);

        if (isset($data['password'])) { //check if there is password change
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT); //if true then hash the password
        }
        $user = $this->userModel->find($id); //check if user exists

        if(!$user){
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'User not found',
                'data' => $id,
                'errors' => '404'
            ]);
        }
        if(isset($data['email'])){
            if($this->userModel->where('email', $data['email'])->where('id !=', $id)->first()){
                return $this->response->setJSON([
                    'status' => 'failed',
                    'message' => 'Email already exists',
                    'data' => $data['email'],
                    'errors' => '409'
                ]);
            }
        }
        $this->userModel->update($id, $data);
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'User updated',
            'data' => $this->userModel->find($id),
            'errors' => '-'
        ]);

    }

    public function delete($id = null){
        $user = $this->userModel->find($id);

        if(!$user){
            return $this->response->setJSON([
                'status' => 'failed',
                'message' => 'User not found',
                'data' => $id,
                'errors' => '404'
            ]);
        }
        $this->userModel->delete($id);
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'User deleted successfully',
            'data' => $id,
            'errors' => '-'
        ]);

    }

    public function updatePassword($id = null)
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['password']) || empty($data['password'])) {
            return $this->failValidationErrors('Password is required');
        }

        $result = $this->userService->update($id, ['password' => $data['password']]);

        if (isset($result['error'])) {
            if (($result['code'] ?? 500) === 404) {
                return $this->failNotFound($result['error']);
            }

            return $this->fail($result['error'], $result['code'] ?? 400);
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Password updated successfully',
            'data' => null,
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
