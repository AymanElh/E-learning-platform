<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\LoginRequest;
use App\Http\Requests\V1\RegisterRequest;
use App\Http\Requests\V1\UpdateProfileRequest;
use App\Models\User;
use App\Repositories\AuthRepository;
use Auth;
use Illuminate\Http\Request;
use Mockery\Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected AuthRepository $authRepository;

    /**
     * @param AuthRepository $authRepository
     */
    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $user = $this->authRepository->register($request->validated());
            if(!$user) {
                return response()->json([
                    'success' => false,
                    'message' => "Registration failed"
                ], 500);
            }
            $token = $this->authRepository->login([
                'email' => $request->email,
                'password' => $request->password
            ]);
            return $this->createNewToken($token);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Registration failed"
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        $token = $this->authRepository->login($request->all());
        if(!$token) {
            return response()->json([
                'success' => false,
                'message' => "Login Failed"
            ], 401);
        }
        return $this->createNewToken($token);
    }

    public function user()
    {
        $user = $this->authRepository->getAuthenticatedUser();
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function logout()
    {
        $success = $this->authRepository->logout();
        if(!$success) {
            return response()->json([
                'success' => false,
                'message' => "Failed to logout"
            ], 500);
        }
        return response()->json([
            'success' => true,
            'message' => "Logout successfully"
        ]);
    }

    public function refresh()
    {
        try {
            $token = $this->authRepository->refreshToken();
            return $this->createNewToken($token);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh token'
            ], 401);
        }
    }

    public function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }


    public function profile()
    {
        return response()->json(['user' => auth()->user()]);
    }

    public function uploadProfilePicture(Request $request)
    {
        $user = $this->authRepository->getAuthenticatedUser();
        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => "Unauthorized"
            ], 401);
        }

        $updatedUser = $this->authRepository->uploadProfilePicture($request->file('profile_picture'), $user);

        return response()->json([
            'success' => true,
            'message' => "Profile Picture updated successfully",
            'data' => [
                'user' => $updatedUser
            ]
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $user = $this->authRepository->updateProfile($request->validated());
            return response()->json([
                'success' => true,
                'message' => "Profile updated successfully",
                'user' => $user
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 405);
        }
    }
}
