<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| CCTV Dashboard API with selective versioning
| - Auth endpoints (login, register, logout, me): No versioning
| - Other endpoints: Use /v1/ prefix
|
*/

// Public authentication routes (no versioning)
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Sanctum protected routes (no versioning)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
});

// V1 API routes (with versioning)
Route::prefix('v1')->group(function () {
    require __DIR__ . '/api_v1.php';
});
