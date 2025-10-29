<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
// use App\Models\ApiCredential; // Model not implemented yet
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

        // TODO: Implement ApiCredential model
        // For now, reject all API key authentication attempts
        Log::warning('API key authentication attempted but ApiCredential model not implemented', ['api_key' => $apiKey, 'ip' => $request->ip()]);
        return ApiResponseHelper::error('API key authentication not implemented', 'NOT_IMPLEMENTED', null, 501);
    }
}
