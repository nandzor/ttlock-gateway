<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Static Token API Routes
|--------------------------------------------------------------------------
|
| Routes yang menggunakan static token authentication.
| Header: x-token: your-static-token
|
*/

Route::middleware('static.token')->group(function () {
    Route::get('/health', function () {
        return response()->json(['success' => true, 'message' => 'Static API healthy']);
    });
});

