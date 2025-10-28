<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\HpsEmasController;
use App\Http\Controllers\Api\V1\HpsElektronikController;
use App\Http\Controllers\Api\V1\TTLockCallbackController;

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

// HPS Elektronik price checking API (requires static token via x-token header)
Route::middleware('static.token')->post('/hps-elektronik/check-price', [HpsElektronikController::class, 'checkPrice'])->name('v1.hps-elektronik.check-price');

// HPS Emas price checking API (requires static token via x-token header)
Route::middleware('static.token')->post('/hps-emas/check-price', [HpsEmasController::class, 'checkPrice'])->name('v1.hps-emas.check-price');


// HPS Emas price checking API (requires static token via x-token header)
Route::post('/ttlock-callback', [TTLockCallbackController::class, 'callback'])->name('v1.ttlock.callback');
