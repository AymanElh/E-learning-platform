<?php

namespace App\Interfaces;

interface AuthRepositoryInterface
{
    public function register(array $data);
    public function login(array $credentials);
    public function refreshToken();
    public function logout();
    public function getAuthenticatedUser();
    public function uploadProfilePicture($file, $user);
    public function updateProfile(array $data);
}
