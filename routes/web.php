<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TTLockCallbackHistoryController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect('/login');
    });
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Monitoring routes removed

// Horizon routes removed

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User CRUD
    Route::resource('users', UserController::class);

    // TTLock Callback Histories
    Route::get('/ttlock-callback-histories', [TTLockCallbackHistoryController::class, 'index'])->name('ttlock.callback.histories.index');
    Route::get('/ttlock-callback-histories/export/{format}', [TTLockCallbackHistoryController::class, 'export'])->name('ttlock.callback.histories.export');

    // Removed routes for modules that are no longer present
});

