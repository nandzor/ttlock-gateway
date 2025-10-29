<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\TTLockCallbackHistory;
use App\Services\TTLockCallbackHistoryService;

/**
 * TTLock Callback Controller
 *
 * Handles callbacks from TTLock platform (https://euopen.ttlock.com/)
 * Processes various lock-related events and operations
 *
 * @package App\Http\Controllers\Api\V1
 */
class TTLockCallbackController extends BaseController
{
    public function __construct(private TTLockCallbackHistoryService $historyService)
    {
    }
    /**
     * Default pagination per page
     */
    private const DEFAULT_PER_PAGE = 20;

    /**
     * Recent callbacks hours threshold
     */
    private const RECENT_HOURS = 24;

    /**
     * Event type mappings
     */
    private const EVENT_TYPE_MESSAGES = [
        'lock_operation' => 'Lock operation received',
        'passcode_operation' => 'Passcode operation received',
        'card_operation' => 'Card operation received',
        'fingerprint_operation' => 'Fingerprint operation received',
        'remote_unlock' => 'Remote unlock operation received',
        'gateway_offline' => 'Gateway is offline',
        'gateway_online' => 'Gateway is online',
        'battery_low' => 'Lock battery is low',
        'tamper_alarm' => 'Lock tamper alarm triggered',
        'security_alert' => 'Security alert triggered',
        'unknown' => 'Unknown callback type',
    ];

    /**
     * Special vendor code messages
     */
    private const VENDOR_CODE_MESSAGES = [
        20 => 'Unlocked via fingerprint',
        29 => 'Unexpected unlock detected',
        44 => 'Tamper alert triggered',
        45 => 'Auto lock activated',
        48 => 'Invalid passcode used multiple times',
    ];
    /**
     * Handle TTLock callback requests
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function callback(Request $request): JsonResponse
    {
        try {
            $this->logCallbackReceived($request);

            $callbackHistory = $this->historyService->processCallback($request);

            $this->logCallbackProcessed($callbackHistory, $callbackHistory->event_type, [
                'recordType' => $callbackHistory->record_type,
            ]);

            return $this->successResponse([
                'callback_id' => $callbackHistory->id,
                'event_type' => $callbackHistory->event_type,
                'message' => $callbackHistory->message,
            ], 'Callback processed and saved successfully');

        } catch (\Exception $e) {
            $this->logCallbackError($e, $request);
            return $this->serverErrorResponse('Failed to process callback');
        }
    }

    /**
     * Log incoming callback for debugging
     *
     * @param Request $request
     * @return void
     */
    private function logCallbackReceived(Request $request): void
    {
        Log::info('TTLock Callback Received', [
            'data' => $request->all(),
            'headers' => $request->headers->all(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Extract callback data from request
     *
     * @param Request $request
     * @return array
     */
    private function extractCallbackData(Request $request): array
    {
        return [
            'lockId' => $request->input('lockId'),
            'lockMac' => $request->input('lockMac'),
            'admin' => $request->input('admin'),
            'notifyType' => $request->input('notifyType'),
            'records' => $this->parseRecords($request->input('records')),
            'requestId' => $request->input('request_id'),
        ];
    }

    /**
     * Parse records from JSON string or array
     *
     * @param mixed $records
     * @return array|null
     */
    private function parseRecords($records): ?array
    {
        if (!$records) {
            return null;
        }

        return is_string($records) ? json_decode($records, true) : $records;
    }

    /**
     * Extract first record from parsed records
     *
     * @param array|null $records
     * @return array
     */
    private function extractFirstRecord(?array $records): array
    {
        if (!$records || !is_array($records) || empty($records)) {
            return [];
        }

        return $records[0];
    }

    /**
     * Create callback history record
     *
     * @param array $callbackData
     * @param array $firstRecord
     * @param string $eventType
     * @param string $message
     * @param Request $request
     * @return TTLockCallbackHistory
     */
    private function createCallbackHistory(array $callbackData, array $firstRecord, string $eventType, string $message, Request $request): TTLockCallbackHistory
    {
        return TTLockCallbackHistory::create([
            'lock_id' => $callbackData['lockId'],
            'lock_mac' => $callbackData['lockMac'],
            'admin' => $callbackData['admin'],
            'notify_type' => $callbackData['notifyType'],
            'records' => $callbackData['records'],
            'record_type_from_lock' => $firstRecord['recordTypeFromLock'] ?? null,
            'record_type' => $firstRecord['recordType'] ?? null,
            'success' => $firstRecord['success'] ?? null,
            'username' => $firstRecord['username'] ?? null,
            'keyboard_pwd' => $firstRecord['keyboardPwd'] ?? null,
            'lock_date' => $firstRecord['lockDate'] ?? null,
            'server_date' => $firstRecord['serverDate'] ?? null,
            'electric_quantity' => $firstRecord['electricQuantity'] ?? null,
            'event_type' => $eventType,
            'message' => $message,
            'raw_data' => $request->all(),
            'request_id' => $callbackData['requestId'],
            'processed' => true,
            'processed_at' => now(),
        ]);
    }

    /**
     * Log callback processed successfully
     *
     * @param TTLockCallbackHistory $callbackHistory
     * @param string $eventType
     * @param array $firstRecord
     * @return void
     */
    private function logCallbackProcessed(TTLockCallbackHistory $callbackHistory, string $eventType, array $firstRecord): void
    {
        Log::info('TTLock Callback Processed and Saved', [
            'callback_id' => $callbackHistory->id,
            'lock_id' => $callbackHistory->lock_id,
            'event_type' => $eventType,
            'record_type' => $firstRecord['recordType'] ?? null,
        ]);
    }

    /**
     * Log callback processing error
     *
     * @param \Exception $e
     * @param Request $request
     * @return void
     */
    private function logCallbackError(\Exception $e, Request $request): void
    {
        Log::error('TTLock Callback Processing Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all(),
        ]);
    }


    /**
     * Determine event type based on record type and recordTypeFromLock
     * Based on TTLock API v3 documentation: https://euopen.ttlock.com/doc/api/v3/lockRecord/list
     *
     * @param int|null $recordType
     * @param int|null $recordTypeFromLock
     * @return string
     */
    // moved to service

    /**
     * Get event type mapping for recordTypeFromLock (vendor-specific codes)
     */
    // moved to service

    /**
     * Get event type mapping for standard recordType codes
     */
    // moved to service

    /**
     * Build human readable message for event type
     * Based on TTLock API v3 documentation: https://euopen.ttlock.com/doc/api/v3/lockRecord/list
     *
     * @param string $eventType
     * @param array $record
     * @return string
     */
    // moved to service

    /**
     * Get callback history with pagination
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getHistory(Request $request): JsonResponse
    {
        try {
            $history = $this->historyService->historyForApi($request);

            return $this->successResponse($history, 'Callback history retrieved successfully');

        } catch (\Exception $e) {
            Log::error('TTLock Callback History Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->serverErrorResponse('Failed to retrieve callback history');
        }
    }

    /**
     * Get callback statistics
     *
     * @return JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $stats = $this->historyService->statistics();

            return $this->successResponse($stats, 'Callback statistics retrieved successfully');

        } catch (\Exception $e) {
            Log::error('TTLock Callback Statistics Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->serverErrorResponse('Failed to retrieve callback statistics');
        }
    }

    /**
     * Extract history filters from request
     *
     * @param Request $request
     * @return array
     */
    // moved to service

    /**
     * Build history query with filters
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // moved to service

    /**
     * Build statistics data
     *
     * @return array
     */
    // moved to service
}
