<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateStaticToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configured = (string) (config('api.static_token') ?? env('API_STATIC_TOKEN', ''));

        if ($configured === '') {
            return response()->json([
                'success' => false,
                'message' => 'Static token not configured'
            ], 500);
        }

        // Prefer Authorization: Bearer <token>
        $provided = $this->extractBearerToken($request);
        // Fallbacks: X-Token or X-API-Token
        if ($provided === null) {
            $provided = $request->header('x-token') ?? $request->header('X-API-Token');
        }

        if (!is_string($provided) || hash_equals($configured, (string) $provided) === false) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Invalid static token'
            ], 401);
        }

        return $next($request);
    }

    private function extractBearerToken(Request $request): ?string
    {
        $authorization = $request->header('Authorization');
        if (!is_string($authorization)) {
            return null;
        }
        if (preg_match('/^Bearer\s+(.*)$/i', $authorization, $matches) === 1) {
            return $matches[1];
        }
        return null;
    }
}

