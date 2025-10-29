<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\TTLockService;

/**
 * TTLock Lock Controller
 *
 * Handles lock operations with TTLock platform
 * Manages unlock, lock, and status operations
 */
class TTLockLockController extends BaseController
{
    protected $ttlockService;

    public function __construct(TTLockService $ttlockService)
    {
        $this->ttlockService = $ttlockService;
    }

    /**
     * Unlock TTLock
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlock(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lockId' => 'nullable|string',
                'date' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse(
                    $validator->errors(),
                    'Invalid unlock request data'
                );
            }

            // Use lockId from request or environment variable
            $lockId = $request->input('lockId', env('TTLOCK_LOCKID'));
            $date = $request->input('date');

            if (empty($lockId)) {
                return $this->validationErrorResponse(
                    ['lockId' => ['Lock ID is required either in request or environment variable TTLOCK_LOCKID']],
                    'Lock ID not provided'
                );
            }

            Log::info('TTLock Lock Controller: Unlocking lock', [
                'lock_id' => $lockId,
                'date' => $date,
                'timestamp' => now(),
            ]);

            $result = $this->ttlockService->unlockLock($lockId, $date);

            if ($result['success']) {
                return $this->successResponse($result['data'], $result['message']);
            } else {
                return $this->serverErrorResponse($result['message']);
            }

        } catch (\Exception $e) {
            Log::error('TTLock Lock Controller: Unlock exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->serverErrorResponse('Failed to process unlock request');
        }
    }

    /**
     * Lock TTLock
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function lock(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lockId' => 'nullable|string',
                'date' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse(
                    $validator->errors(),
                    'Invalid lock request data'
                );
            }

            // Use lockId from request or environment variable
            $lockId = $request->input('lockId', env('TTLOCK_LOCKID'));
            $date = $request->input('date');

            if (empty($lockId)) {
                return $this->validationErrorResponse(
                    ['lockId' => ['Lock ID is required either in request or environment variable TTLOCK_LOCKID']],
                    'Lock ID not provided'
                );
            }

            Log::info('TTLock Lock Controller: Locking lock', [
                'lock_id' => $lockId,
                'date' => $date,
                'timestamp' => now(),
            ]);

            $result = $this->ttlockService->lockLock($lockId, $date);

            if ($result['success']) {
                return $this->successResponse($result['data'], $result['message']);
            } else {
                return $this->serverErrorResponse($result['message']);
            }

        } catch (\Exception $e) {
            Log::error('TTLock Lock Controller: Lock exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->serverErrorResponse('Failed to process lock request');
        }
    }

    /**
     * Get lock status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lockId' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse(
                    $validator->errors(),
                    'Invalid status request data'
                );
            }

            // Use lockId from request or environment variable
            $lockId = $request->input('lockId', env('TTLOCK_LOCKID'));

            if (empty($lockId)) {
                return $this->validationErrorResponse(
                    ['lockId' => ['Lock ID is required either in request or environment variable TTLOCK_LOCKID']],
                    'Lock ID not provided'
                );
            }

            Log::info('TTLock Lock Controller: Getting lock status', [
                'lock_id' => $lockId,
                'timestamp' => now(),
            ]);

            $result = $this->ttlockService->getLockStatus($lockId);

            if ($result['success']) {
                return $this->successResponse($result['data'], $result['message']);
            } else {
                return $this->serverErrorResponse($result['message']);
            }

        } catch (\Exception $e) {
            Log::error('TTLock Lock Controller: Status exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->serverErrorResponse('Failed to process status request');
        }
    }
}
