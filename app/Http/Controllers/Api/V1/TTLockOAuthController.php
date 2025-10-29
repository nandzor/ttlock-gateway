<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\TTLockService;

/**
 * TTLock OAuth Controller
 *
 * Handles OAuth2 authentication with TTLock platform
 * Manages token requests and authentication flow
 */
class TTLockOAuthController extends BaseController
{
    protected $ttlockService;

    public function __construct(TTLockService $ttlockService)
    {
        $this->ttlockService = $ttlockService;
    }

    /**
     * Get OAuth2 access token from TTLock
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken(Request $request)
    {
        try {
            Log::info('TTLock OAuth Controller: Getting token', [
                'timestamp' => now(),
            ]);

            $result = $this->ttlockService->getAccessToken();

            if ($result['success']) {
                return $this->successResponse($result['data'], $result['message']);
            } else {
                return $this->serverErrorResponse($result['message']);
            }

        } catch (\Exception $e) {
            Log::error('TTLock OAuth Controller: Get token exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->serverErrorResponse('Failed to process TTLock OAuth token request');
        }
    }

    /**
     * Refresh OAuth2 access token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'refresh_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse(
                    $validator->errors(),
                    'Invalid refresh token data'
                );
            }

            Log::info('TTLock OAuth Controller: Refreshing token', [
                'timestamp' => now(),
            ]);

            $result = $this->ttlockService->refreshAccessToken($request->input('refresh_token'));

            if ($result['success']) {
                return $this->successResponse($result['data'], $result['message']);
            } else {
                return $this->serverErrorResponse($result['message']);
            }

        } catch (\Exception $e) {
            Log::error('TTLock OAuth Controller: Refresh token exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->serverErrorResponse('Failed to process TTLock OAuth refresh token request');
        }
    }

    /**
     * Get TTLock configuration status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfigStatus()
    {
        try {
            Log::info('TTLock OAuth Controller: Getting config status', [
                'timestamp' => now(),
            ]);

            $result = $this->ttlockService->getConfigStatus();

            return $this->successResponse($result['data'], $result['message']);

        } catch (\Exception $e) {
            Log::error('TTLock OAuth Controller: Get config status exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->serverErrorResponse('Failed to get TTLock configuration status');
        }
    }
}
