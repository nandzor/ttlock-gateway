<?php

namespace App\Services;

use App\Models\TTLockCallbackHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class TTLockCallbackHistoryService extends BaseService
{
    /**
     * Default pagination per page for API
     */
    public const DEFAULT_PER_PAGE = 20;

    /**
     * Recent hours window for statistics
     */
    public const RECENT_HOURS = 24;

    /**
     * Event type mappings - shared between vendor and standard codes
     */
    private const EVENT_TYPE_MAP = [
        'lock_operation' => [
            'codes' => [1, 10, 11, 29, 30, 31, 32, 33, 34, 35, 36, 45, 46, 47],
            'vendor_codes' => [1, 10, 11, 29, 30, 31, 32, 33, 34, 35, 36, 45, 46, 47],
        ],
        'passcode_operation' => [
            'codes' => [2, 4],
            'vendor_codes' => [4],
        ],
        'card_operation' => [
            'codes' => [3, 7],
            'vendor_codes' => [7],
        ],
        'fingerprint_operation' => [
            'codes' => [8],
            'vendor_codes' => [8, 20],
        ],
        'remote_unlock' => [
            'codes' => [5, 12, 37],
            'vendor_codes' => [12, 37],
        ],
        'gateway_offline' => [
            'codes' => [6],
            'vendor_codes' => [],
        ],
        'gateway_online' => [
            'codes' => [],
            'vendor_codes' => [],
        ],
        'tamper_alarm' => [
            'codes' => [9, 44],
            'vendor_codes' => [44],
        ],
        'security_alert' => [
            'codes' => [48],
            'vendor_codes' => [48],
        ],
    ];

    /**
     * Event type messages
     */
    private const EVENT_MESSAGES = [
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
     * Vendor code specific message overrides
     */
    private const VENDOR_MESSAGE_OVERRIDES = [
        20 => 'Unlocked via fingerprint',
        29 => 'Unexpected unlock detected',
        44 => 'Tamper alert triggered',
        45 => 'Auto lock activated',
        48 => 'Invalid passcode used multiple times',
    ];

    public function __construct(TTLockCallbackHistory $model)
    {
        $this->model = $model;
        $this->searchableFields = [
            'lock_id',
            'lock_mac',
            'username',
            'message',
        ];
        $this->orderByColumn = 'created_at';
        $this->orderByDirection = 'desc';
    }

    /**
     * Get collection for export based on request filters/search/order
     */
    public function listForExport(Request $request): Collection
    {
        $filters = $this->buildFiltersFromRequest($request, [
            'lock_id', 'event_type', 'record_type', 'processed', 'date_from', 'date_to', 'username', 'lock_mac'
        ]);

        $search = $request->input('search');
        $orderBy = $request->input('order_by');
        $direction = $request->input('order_dir');

        $query = $this->getBaseQuery();

        if (!empty($filters)) {
            $query = $this->applyFilters($query, $filters);
        }

        if (!empty($search)) {
            $query = $this->applySearch($query, $search);
        }

        $query = $this->applyOrdering($query, $orderBy, $direction);

        return $query->get();
    }

    /**
     * Process incoming TTLock callback and persist history
     */
    public function processCallback(Request $request): TTLockCallbackHistory
    {
        $callbackData = $this->extractCallbackData($request);
        $firstRecord = $this->extractFirstRecord($callbackData['records']);

        $eventType = $this->determineEventType(
            $firstRecord['recordType'] ?? null,
            $firstRecord['recordTypeFromLock'] ?? null
        );

        $message = $this->buildEventMessage($eventType, $firstRecord);

        $history = TTLockCallbackHistory::create([
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

        Log::info('TTLock Callback Processed (service)', [
            'callback_id' => $history->id,
            'lock_id' => $history->lock_id,
            'event_type' => $eventType,
            'record_type' => $firstRecord['recordType'] ?? null,
        ]);

        return $history;
    }

    /**
     * Return paginated API history with basic filters
     */
    public function historyForApi(Request $request): LengthAwarePaginator
    {
        $perPage = (int) $request->get('per_page', self::DEFAULT_PER_PAGE);
        $filters = [
            'lock_id' => $request->get('lock_id'),
            'event_type' => $request->get('event_type'),
            'record_type' => $request->get('record_type'),
            'processed' => $request->get('processed'),
        ];

        $query = $this->getBaseQuery();

        if (!empty($filters['lock_id'])) {
            $query->byLockId($filters['lock_id']);
        }
        if (!empty($filters['event_type'])) {
            $query->byEventType($filters['event_type']);
        }
        if (!empty($filters['record_type'])) {
            $query->byRecordType((int) $filters['record_type']);
        }
        if ($filters['processed'] !== null) {
            $query->where('processed', (bool) $filters['processed']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Build statistics dataset for API
     */
    public function statistics(): array
    {
        return [
            'total_callbacks' => TTLockCallbackHistory::count(),
            'processed_callbacks' => TTLockCallbackHistory::processed()->count(),
            'unprocessed_callbacks' => TTLockCallbackHistory::unprocessed()->count(),
            'recent_callbacks' => TTLockCallbackHistory::recent(self::RECENT_HOURS)->count(),
            'event_types' => TTLockCallbackHistory::selectRaw('event_type, COUNT(*) as count')
                ->groupBy('event_type')
                ->pluck('count', 'event_type'),
            'locks' => TTLockCallbackHistory::selectRaw('lock_id, COUNT(*) as count')
                ->groupBy('lock_id')
                ->pluck('count', 'lock_id'),
        ];
    }


    // ---------- Helpers below (moved from controller) ----------

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

    private function parseRecords($records): ?array
    {
        if (!$records) {
            return null;
        }
        return is_string($records) ? json_decode($records, true) : $records;
    }

    private function extractFirstRecord(?array $records): array
    {
        if (!$records || !is_array($records) || empty($records)) {
            return [];
        }
        return $records[0];
    }

    private function determineEventType(?int $recordType, ?int $recordTypeFromLock = null): string
    {
        // Priority 1: Check vendor-specific codes (recordTypeFromLock)
        if ($recordTypeFromLock !== null) {
            foreach (self::EVENT_TYPE_MAP as $eventType => $mappings) {
                if (in_array($recordTypeFromLock, $mappings['vendor_codes'])) {
                    return $eventType;
                }
            }
        }

        // Priority 2: Check standard codes (recordType)
        if ($recordType !== null) {
            foreach (self::EVENT_TYPE_MAP as $eventType => $mappings) {
                if (in_array($recordType, $mappings['codes'])) {
                    return $eventType;
                }
            }
        }

        return 'unknown';
    }

    private function buildEventMessage(string $eventType, array $record): string
    {
        $baseMessage = self::EVENT_MESSAGES[$eventType] ?? 'Unknown event';

        // Override message for specific vendor codes
        $recordTypeFromLock = $record['recordTypeFromLock'] ?? null;
        if ($recordTypeFromLock && isset(self::VENDOR_MESSAGE_OVERRIDES[$recordTypeFromLock])) {
            $baseMessage = self::VENDOR_MESSAGE_OVERRIDES[$recordTypeFromLock];
        }

        // Add user information
        if (!empty($record['username'])) {
            $baseMessage .= " by user: {$record['username']}";
        }

        // Add battery information
        if (isset($record['electricQuantity'])) {
            $baseMessage .= " (Battery: {$record['electricQuantity']}%)";
        }

        return $baseMessage;
    }

}


