<?php
namespace App\Services;
use App\Models\UserModel;

class UserService
{
    protected $userModel;
    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function list($perPage = 10){
        return $this->userModel->paginate($perPage);
    }

    public function find($id){
        return $this->userModel->find($id);
    }

    public function create($data){
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        if ($this->userModel->withDeleted()->where('email', $data['email'])->first()) {
            return [
                'error' => 'Email already used',
                'code' => 409
            ];
        }
        $inserted = $this->userModel->insert($data);
        if ($inserted === false) {
            return ['error' => $this->userModel->errors()];
        }
        return $this->userModel->find($this->userModel->getInsertID());
    }

    public function update($id, $data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $user = $this->userModel->find($id);

        if(!$user){
            return [
                'error' => 'user not found',
                'code' => 404
            ];
        }
        if(isset($data['email'])){
            if($this->userModel->where('email', $data['email'])->where('id !=', $id)->first()){
                return [
                    'error' => 'email already exists',
                    'code' => 409
                ];
            }
        }
        if(isset($data['status'])){
            return [
                'error' => 'wrong API to activate or suspend',
                'code' => 400
            ];
        }
        $this->userModel->update($id, $data);
        return $this->userModel->find($id);
    }


    public function delete($id){
        $user = $this->userModel->find($id);

        if(!$user){
            return [
                'error' => 'user not found',
                'code' => 404
            ];
        }
        return $this->userModel->delete($id);
    }

    public function activate($id){
        $user = $this->userModel->find($id);
        if (! $user) {
            return [
                'error' => 'User not found',
                'code' => 404,
            ];
        }

        $updated = $this->userModel->update($id, ['status' => 'active']);
        if ($updated === false) {
            return [
                'error' => $this->userModel->errors(),
                'code' => 422,
            ];
        }

        return [
            'success' => true,
            'data' => $this->userModel->find($id),
        ];
    }

    public function suspend($id){
        $user = $this->userModel->find($id);
        if (! $user) {
            return [
                'error' => 'User not found',
                'code' => 404,
            ];
        }

        $updated = $this->userModel->update($id, ['status' => 'suspended']);

        if ($updated === false) {
            return [
                'error' => $this->userModel->errors(),
                'code' => 422,
            ];
        }

        return [
            'success' => true,
            'data' => $this->userModel->find($id),
        ];

    }
}
