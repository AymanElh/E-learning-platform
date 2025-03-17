<?php

namespace App\Repositories;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use Mockery\Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthRepository implements AuthRepositoryInterface
{
    public function register(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password']
        ]);
    }

    public function login(array $credentials)
    {
        if (! $token = JWTAuth::attempt($credentials)) {
            return null;
        }
        return $token;
    }

    public function refreshToken()
    {
        return auth()->refresh();
    }

    public function logout(): bool
    {
        try {
            auth()->logout();
            return true;
        } catch (Exception $e) {
            \Log::error("Error logout: " . $e->getMessage());
            return false;
        }
    }

    public function getAuthenticatedUser(): User|\Illuminate\Contracts\Auth\Authenticatable|null
    {
        try {
            return auth()->user();
        } catch (Exception $e) {
            return null;
        }
    }

}
