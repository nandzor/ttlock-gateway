<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\TTLockCallbackController;
use App\Http\Controllers\Api\V1\TTLockOAuthController;
use App\Http\Controllers\Api\V1\TTLockLockController;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Version 1 of the CCTV Dashboard API
| Base URL: /api/v1/
| Status: Current (Latest)
| Released: October 2025
|
*/

// User management routes - using session-based auth with proper API response
Route::middleware('api.session|auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class)->names([
        'index' => 'v1.users.index',
        'store' => 'v1.users.store',
        'show' => 'v1.users.show',
        'update' => 'v1.users.update',
        'destroy' => 'v1.users.destroy',
    ]);

    Route::get('/users/pagination/options', [UserController::class, 'paginationOptions'])->name('v1.users.pagination.options');
});



Route::post('/ttlock-callback', [TTLockCallbackController::class, 'callback'])->name('v1.ttlock.callback');
Route::get('/ttlock-callback/history', [TTLockCallbackController::class, 'getHistory'])->name('v1.ttlock.callback.history');
Route::get('/ttlock-callback/statistics', [TTLockCallbackController::class, 'getStatistics'])->name('v1.ttlock.callback.statistics');

// TTLock OAuth2 API routes
Route::prefix('ttlock')->group(function () {
    Route::post('/oauth/token', [TTLockOAuthController::class, 'getToken'])->name('v1.ttlock.oauth.token');
    Route::post('/oauth/refresh', [TTLockOAuthController::class, 'refreshToken'])->name('v1.ttlock.oauth.refresh');
    Route::get('/config/status', [TTLockOAuthController::class, 'getConfigStatus'])->name('v1.ttlock.config.status');

    // TTLock Lock operations
    Route::post('/lock/unlock', [TTLockLockController::class, 'unlock'])->name('v1.ttlock.lock.unlock');
    Route::post('/lock/lock', [TTLockLockController::class, 'lock'])->name('v1.ttlock.lock.lock');
    Route::get('/lock/status', [TTLockLockController::class, 'status'])->name('v1.ttlock.lock.status');
});
