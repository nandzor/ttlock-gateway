<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiCredential;
use App\Helpers\ApiResponseHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiKeyAuth {
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response {
        $apiKey = $request->header('X-API-Key');
        $apiSecret = $request->header('X-API-Secret');

        // Check if API key is provided
        if (!$apiKey || !$apiSecret) {
            return ApiResponseHelper::unauthorized('API key and secret are required');
        }

        // Find API credential with caching for performance
        $credential = Cache::remember("api_credential:{$apiKey}", 300, function () use ($apiKey) {
            return ApiCredential::findByApiKey($apiKey);
        });

        if (!$credential) {
            Log::warning('Invalid API key attempt', ['api_key' => $apiKey, 'ip' => $request->ip()]);
            return ApiResponseHelper::error('Invalid API credentials', 'INVALID_CREDENTIALS', null, 401);
        }

        // Verify API secret using hash_equals (timing attack safe)
        if (!hash_equals($credential->api_secret, $apiSecret)) {
            Log::warning('Invalid API secret attempt', ['api_key' => $apiKey, 'ip' => $request->ip()]);
            return ApiResponseHelper::error('Invalid API credentials', 'INVALID_CREDENTIALS', null, 401);
        }

        // Check if credential is expired
        if ($credential->isExpired()) {
            return ApiResponseHelper::error('API credentials expired', 'EXPIRED_CREDENTIALS', null, 401);
        }

        // Rate limiting - DISABLED FOR TESTING
        $rateLimitKey = "api_rate_limit:{$credential->api_key}";
        $hourlyRequests = Cache::get($rateLimitKey, 0);

        if ($hourlyRequests >= $credential->rate_limit) {
            return ApiResponseHelper::error(
                'Rate limit exceeded. Try again later.',
                'RATE_LIMIT_EXCEEDED',
                [
                    'limit' => $credential->rate_limit,
                    'period' => 'hour',
                    'reset_at' => now()->startOfHour()->addHour()->toIso8601String(),
                ],
                429
            );
        }

        // Increment rate limit counter
        if ($hourlyRequests === 0) {
            Cache::put($rateLimitKey, 1, now()->endOfHour());
        } else {
            Cache::increment($rateLimitKey);
        }

        // Update last_used_at (async to not slow down request)
        dispatch(function () use ($credential) {
            $credential->update(['last_used_at' => now()]);
        })->afterResponse();

        // Attach credential to request for later use
        $request->merge(['api_credential' => $credential]);

        // Add rate limit headers - DISABLED FOR TESTING
        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', $credential->rate_limit);
        $response->headers->set('X-RateLimit-Remaining', max(0, $credential->rate_limit - $hourlyRequests - 1));
        $response->headers->set('X-RateLimit-Reset', now()->startOfHour()->addHour()->timestamp);

        return $response;
    }
}
