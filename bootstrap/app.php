<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('api/static')
                ->middleware('api')
                ->group(base_path('routes/api-static.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust proxies for HTTPS support behind ngrok
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Add RequestResponseInterceptor to API middleware group for daily access logging
        $middleware->api(append: [
            \App\Http\Middleware\RequestResponseInterceptor::class,
        ]);

        $middleware->alias([
            'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'static.token' => \App\Http\Middleware\ValidateStaticToken::class,
            'admin' => \App\Http\Middleware\AdminOnly::class,
            'api.key' => \App\Http\Middleware\ApiKeyAuth::class,
            'api.version' => \App\Http\Middleware\ApiVersion::class,
            'web.session' => \App\Http\Middleware\AllowWebSessionAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
