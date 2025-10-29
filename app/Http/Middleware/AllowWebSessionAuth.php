<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to allow API routes to use web session authentication
 * This enables authenticated web users to access API endpoints via AJAX
 */
class AllowWebSessionAuth
{
    protected $startSession;
    protected $encryptCookies;

    public function __construct(StartSession $startSession, EncryptCookies $encryptCookies)
    {
        $this->startSession = $startSession;
        $this->encryptCookies = $encryptCookies;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // First decrypt cookies
        return $this->encryptCookies->handle($request, function ($req) use ($next) {
            // Then start session
            return $this->startSession->handle($req, function ($r) use ($next) {
                // Now check if user is authenticated via web session
                if (Auth::guard('web')->check()) {
                    return $next($r);
                }

                // If not authenticated, return unauthorized
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            });
        });
    }
}
