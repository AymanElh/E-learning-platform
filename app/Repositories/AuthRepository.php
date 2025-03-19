<?php

namespace App\Repositories;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthRepository implements AuthRepositoryInterface
{
    public function register(array $data)
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password']
        ];

        if($data['profile_picture']) {
            $path = $data['profile_picture']->store('profile_pictures', 'public');
            $userData['profile_picture'] = $path;
        }

        return User::create($userData);
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
            $user =  auth()->user();
            if($user) {
                $user->load('roles', 'permissions');
                $user->role_names = $user->getRoleNames();
                $user->permission_names = $user->getAllPermissions()->pluck('name');
            }
        } catch (Exception $e) {
            return null;
        }
    }

    public function uploadProfilePicture($file, $user)
    {
        if($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }
        $path = $file->store('images', 'public');
//        dd($user->profile_picture);
        $user->update(['profile_picture' => $path]);

        return $user;
    }
}
