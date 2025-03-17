<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\LoginRequest;
use App\Http\Requests\V1\RegisterRequest;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();
//        return response()->json([
//            'data' => $validatedData,
//        ]);
        try {

            $user = User::create($validatedData);
//            $token = JWTAuth::fromUser($user);
            \Log::info("Registration user with ID: " . $user->id);

            return response()->json([
                'success' => true,
                'message' => "User registered successfully",
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            \Log::error("Error during user registration: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Registration failed: " . $e->getMessage()
            ], 400);
        }
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->credentials();
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = auth()->user();

            return $this->createNewToken($token);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
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
}
