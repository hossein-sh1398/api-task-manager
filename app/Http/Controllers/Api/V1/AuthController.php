<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create($request->validated());
    
            return $this->successResponse([
                'user' => new UserResource($user),
                'api_token' =>  $user->generateToken($request->ip())
            ], 'ثبت نام با موفقیت انجام شد', 201);
        } catch(Exception $e) {
            return $this->errorResponse('خطا در ثبت‌ نام: ' . $e->getMessage(), 500);
        }
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (!$user || !Hash::check($request->validated('password'), $user->password)) {
            return $this->errorResponse('ایمیل یا رمز عبور اشتباه است', 401);
        }

        try {
            return $this->successResponse([
                'user' => new UserResource($user),
                'api_token' => $user->generateToken($request->ip())
            ], 'ورود با موفقیت انجام شد', 200);
        } catch (Exception $e) {
            return $this->errorResponse('خطا در تولید توکن: ' . $e->getMessage(), 500);
        }
    }

    public function logout()
    {
        \request()->user()->tokens()->delete();

        return $this->successResponse(null);
    }
}