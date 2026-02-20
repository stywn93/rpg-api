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
        $inserted = $this->userModel->insert($data);
        if ($inserted === false) {
            return ['error' => $this->userModel->errors()];
        }
        return $this->userModel->find($this->userModel->getInsertID());
    }

    public function update($id, $data){
        if(isset($data['password'])){
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $this->userModel->update($id, $data);
        return $this->userModel->find($id);
    }

    public function delete($id){
        $this->userModel->delete($id);
    }

    public function activate($id){
        return "abcd";

//        $user = $this->userModel->update($id, ['status' => 'active']);
//        $db = \Config\Database::connect();
//        echo $db->getLastQuery();
    }

    public function suspend($id){
        $user = $this->userModel->update($id, ['status' => 'suspended']);
    }
}
