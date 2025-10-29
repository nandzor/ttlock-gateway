<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Services\TTLockService;

/**
 * Dashboard Controller
 *
 * Handles dashboard-related API endpoints
 * Provides gateway and lock management functionality
 */
class DashboardController extends BaseController
{
    protected $ttlockService;

    public function __construct(TTLockService $ttlockService)
    {
        $this->ttlockService = $ttlockService;
    }

    /**
     * Get locks by gateway ID
     *
     * @param Request $request
     * @param int $gatewayId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLocksByGateway(Request $request, $gatewayId)
    {
        try {
            // Validate gateway ID
            $validator = Validator::make(['gatewayId' => $gatewayId], [
                'gatewayId' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse(
                    $validator->errors(),
                    'Invalid gateway ID'
                );
            }

            // Get pagination parameters from request
            $pageNo = $request->input('pageNo', 1);
            $pageSize = $request->input('pageSize', 100);

            // Validate pagination parameters
            $pageNo = max(1, (int) $pageNo);
            $pageSize = min(200, max(1, (int) $pageSize));

            Log::info('Dashboard API: Getting locks by gateway', [
                'gateway_id' => $gatewayId,
                'page_no' => $pageNo,
                'page_size' => $pageSize,
                'user_id' => Auth::check() ? Auth::id() : null,
            ]);

            // Get locks from TTLock service
            $locksResponse = $this->ttlockService->getLocksByGateway($gatewayId, $pageNo, $pageSize);

            if ($locksResponse['success']) {
                $locksData = $locksResponse['data']['raw_response'] ?? [];
                $locks = $locksData['list'] ?? [];

                return $this->successResponse([
                    'locks' => $locks,
                    'pagination' => [
                        'total' => count($locks),
                        'page_no' => 1,
                        'page_size' => count($locks),
                        'pages' => 1,
                    ],
                ], 'Locks retrieved successfully');
            } else {
                return $this->errorResponse(
                    $locksResponse['message'] ?? 'Failed to get locks',
                    ['gateway_id' => $gatewayId],
                    400
                );
            }
        } catch (\Exception $e) {
            Log::error('Dashboard API: Get locks by gateway failed', [
                'gateway_id' => $gatewayId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->serverErrorResponse('Failed to retrieve locks: ' . $e->getMessage());
        }
    }

    /**
     * Get gateway status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGatewayStatus(Request $request)
    {
        try {
            Log::info('Dashboard API: Getting gateway status', [
                'user_id' => Auth::check() ? Auth::id() : null,
            ]);

            $gatewayStatus = $this->ttlockService->getGatewayStatus();

            if ($gatewayStatus['success']) {
                return $this->successResponse(
                    $gatewayStatus['data'],
                    $gatewayStatus['message'] ?? 'Gateway status retrieved successfully'
                );
            } else {
                return $this->errorResponse(
                    $gatewayStatus['message'] ?? 'Failed to get gateway status',
                    null,
                    400
                );
            }
        } catch (\Exception $e) {
            Log::error('Dashboard API: Get gateway status failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->serverErrorResponse('Failed to retrieve gateway status: ' . $e->getMessage());
        }
    }
}
