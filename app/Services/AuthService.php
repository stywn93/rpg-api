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
        if ($this->userModel->where('email', $data['email'])->first()) {
            return ['error' => 'Email already exists'];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $res = $this->userModel->insert($data);

        return ['success' => true];
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
            'expires_in' => 7200
        ];
    }
}