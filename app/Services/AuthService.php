<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['role'] = $data['role'] ?? 'user';

        return User::create($data);
    }

    /**
     * Attempt to authenticate user
     */
    public function login(array $credentials, bool $remember = false): bool
    {
        return Auth::attempt($credentials, $remember);
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        Auth::logout();
    }

    /**
     * Get authenticated user
     */
    public function user(): ?User
    {
        return Auth::user();
    }

    /**
     * Create API token for user
     */
    public function createToken(User $user, string $name = 'api-token'): string
    {
        return $user->createToken($name)->plainTextToken;
    }
}

