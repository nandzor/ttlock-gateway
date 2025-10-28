<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $version = 'v1'): Response
    {
        // Add API version to request attributes
        $request->attributes->set('api_version', $version);

        $response = $next($request);

        // Add version headers to response
        if ($response instanceof \Illuminate\Http\JsonResponse || 
            $response instanceof \Illuminate\Http\Response) {
            $response->headers->set('X-API-Version', $version);
            $response->headers->set('X-API-Latest-Version', 'v1');
            $response->headers->set('X-API-Deprecated', 'false');
        }

        return $response;
    }
}
