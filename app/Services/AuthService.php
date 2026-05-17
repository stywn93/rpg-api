<?php

namespace App\Services;

use App\Models\UserModel;
use App\Libraries\JwtLibrary;

class AuthService
{
    protected $userModel;
    protected $jwt;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->jwt = new JwtLibrary();
    }

    public function register($data)
    {
        $data = $this->normalizeRolePayload($data);

        if ($this->userModel->where('email', $data['email'])->first()) {
            return ['error' => 'Email already exists'];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $res = $this->userModel->insert($data);
        if ($res === false) {
                return [
                    'error' => $this->userModel->errors(),
                    'code' => 500
                ];
            }

            $user = $this->userModel->find($this->userModel->getInsertID());
            unset($user['password']); // jangan kirim password ke client
            return $user;
    }


    public function login($email, $password)
    {
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) return ['error' => 'User not found'];

        if($user['status'] === 'suspended') return ['error' => 'User suspended'];

        if (!password_verify($password, $user['password'])) {
            return ['error' => 'Invalid password'];
        }

        $token = $this->jwt->generateToken($user);

        return [
            'token' => $token,
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'alamat' => $user['alamat'] ?? null,
            'peran' => $user['role'],
            'role' => $user['role'],
            'status' => $user['status'],
            'expires_in' => JwtLibrary::TOKEN_TTL_SECONDS
        ];
    }

    private function normalizeRolePayload($data)
    {
        if (! is_array($data)) {
            return [];
        }

        if (isset($data['peran']) && ! isset($data['role'])) {
            $data['role'] = $data['peran'];
        }

        if (! isset($data['role']) || $data['role'] === null || $data['role'] === '') {
            $data['role'] = 'user';
        }

        unset($data['peran']);

        return $data;
    }
}
