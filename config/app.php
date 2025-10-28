<?php

use Illuminate\Support\Facades\Facade;

return [

    'name' => env('APP_NAME', 'Laravel'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    // Enable query logging (use with caution in production)
    'log_queries' => env('DB_LOG_QUERIES', false),

    // Performance monitoring
    'performance_monitoring' => [
        'enabled' => env('PERFORMANCE_MONITORING', true),
        'include_in_response' => env('PERFORMANCE_IN_RESPONSE', true),
        'include_in_headers' => env('PERFORMANCE_IN_HEADERS', true),
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000), // ms
        'high_memory_threshold' => env('HIGH_MEMORY_THRESHOLD', 128), // MB
    ],

];
