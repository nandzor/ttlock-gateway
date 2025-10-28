<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register via API
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $this->authService->register($request->all());
        $token = $this->authService->createToken($user);

        return $this->createdResponse([
            'user' => $user,
            'token' => $token
        ], 'User registered successfully');
    }

    /**
     * Login via API
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        if (!$this->authService->login($request->only('email', 'password'))) {
            return $this->unauthorizedResponse('Invalid credentials');
        }

        $user = $this->authService->user();
        $token = $this->authService->createToken($user);

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'Login successful');
    }

    /**
     * Logout via API
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        return $this->successResponse($request->user(), 'User retrieved successfully');
    }
}

