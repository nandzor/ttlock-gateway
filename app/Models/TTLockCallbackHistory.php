<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TTLockCallbackHistory extends Model
{
    use HasFactory;

    protected $table = 'ttlock_callback_history';

    protected $fillable = [
        'lock_id',
        'lock_mac',
        'admin',
        'notify_type',
        'records',
        'record_type_from_lock',
        'record_type',
        'success',
        'username',
        'keyboard_pwd',
        'lock_date',
        'server_date',
        'electric_quantity',
        'event_type',
        'message',
        'raw_data',
        'request_id',
        'processed',
        'processed_at',
        'processing_notes',
    ];

    protected $casts = [
        'records' => 'array',
        'raw_data' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
        'lock_date' => 'integer',
        'server_date' => 'integer',
        'record_type' => 'integer',
        'record_type_from_lock' => 'integer',
    ];

    /**
     * Get the event type description
     * Based on TTLock API v3 documentation: https://euopen.ttlock.com/doc/api/v3/lockRecord/list
     */
    public function getEventTypeDescriptionAttribute(): string
    {
        $eventTypes = [
            'lock_operation' => 'Lock Operation (Unlock/Lock)',
            'passcode_operation' => 'Passcode Operation',
            'card_operation' => 'Card Operation',
            'fingerprint_operation' => 'Fingerprint Operation',
            'remote_unlock' => 'Remote Unlock',
            'gateway_offline' => 'Gateway Offline',
            'gateway_online' => 'Gateway Online',
            'battery_low' => 'Lock Battery Low',
            'tamper_alarm' => 'Lock Tamper Alarm',
            'security_alert' => 'Security Alert',
            'unknown' => 'Unknown Event',
        ];

        return $eventTypes[$this->event_type] ?? 'Unknown Event';
    }

    /**
     * Get record type description based on numeric record_type and recordTypeFromLock
     * Based on TTLock API v3 documentation: https://euopen.ttlock.com/doc/api/v3/lockRecord/list
     */
    public function getRecordTypeDescriptionAttribute(): string
    {
        // First check recordTypeFromLock for vendor-specific codes
        if ($this->record_type_from_lock !== null) {
            $vendorMap = $this->getRecordTypeFromLockMap();
            if (isset($vendorMap[$this->record_type_from_lock])) {
                return $vendorMap[$this->record_type_from_lock];
            }
        }

        // Then check standard recordType
        if ($this->record_type !== null) {
            $standardMap = $this->getRecordTypeMap();
            return $standardMap[$this->record_type] ?? 'Unknown Record Type';
        }

        return 'Unknown';
    }

    /**
     * Get mapping for recordTypeFromLock (vendor-specific codes)
     */
    private function getRecordTypeFromLockMap(): array
    {
        return [
            1 => 'App Unlock',
            4 => 'Passcode Unlock',
            7 => 'IC Card Unlock',
            8 => 'Fingerprint Unlock',
            10 => 'Mechanical Key Unlock',
            11 => 'Bluetooth Lock',
            12 => 'Gateway Unlock',
            20 => 'Fingerprint Unlock (Vendor)', // Special vendor code
            29 => 'Unexpected Unlock',
            30 => 'Door Magnet Close',
            31 => 'Door Magnet Open',
            32 => 'Open from Inside',
            33 => 'Lock by Fingerprint',
            34 => 'Lock by Passcode',
            35 => 'Lock by IC Card',
            36 => 'Lock by Mechanical Key',
            37 => 'Remote Control',
            44 => 'Tamper Alert',
            45 => 'Auto Lock',
            46 => 'Unlock by Unlock Key',
            47 => 'Lock by Lock Key',
            48 => 'Use Invalid Passcode Several Times',
        ];
    }

    /**
     * Get mapping for standard recordType codes
     */
    private function getRecordTypeMap(): array
    {
        return [
            1 => 'App Unlock',
            2 => 'Passcode Operation',
            3 => 'Card Operation',
            4 => 'Passcode Unlock',
            5 => 'Remote Unlock',
            6 => 'Gateway Offline',
            7 => 'IC Card Unlock',
            8 => 'Fingerprint Unlock',
            9 => 'Tamper Alarm',
            10 => 'Mechanical Key Unlock',
            11 => 'Bluetooth Lock',
            12 => 'Gateway Unlock',
            29 => 'Unexpected Unlock',
            30 => 'Door Magnet Close',
            31 => 'Door Magnet Open',
            32 => 'Open from Inside',
            33 => 'Lock by Fingerprint',
            34 => 'Lock by Passcode',
            35 => 'Lock by IC Card',
            36 => 'Lock by Mechanical Key',
            37 => 'Remote Control',
            44 => 'Tamper Alert',
            45 => 'Auto Lock',
            46 => 'Unlock by Unlock Key',
            47 => 'Lock by Lock Key',
            48 => 'Use Invalid Passcode Several Times',
        ];
    }

    /**
     * Get formatted lock date
     */
    public function getFormattedLockDateAttribute(): ?string
    {
        if (!$this->lock_date) {
            return null;
        }

        return date('Y-m-d H:i:s', $this->lock_date / 1000);
    }

    /**
     * Get formatted server date
     */
    public function getFormattedServerDateAttribute(): ?string
    {
        if (!$this->server_date) {
            return null;
        }

        return date('Y-m-d H:i:s', $this->server_date / 1000);
    }

    /**
     * Get battery level description
     */
    public function getBatteryLevelDescriptionAttribute(): string
    {
        if ($this->electric_quantity === null) {
            return 'Unknown';
        }

        if ($this->electric_quantity >= 80) {
            return 'High';
        } elseif ($this->electric_quantity >= 50) {
            return 'Medium';
        } elseif ($this->electric_quantity >= 20) {
            return 'Low';
        } else {
            return 'Critical';
        }
    }

    /**
     * Scope: Processed callbacks
     */
    public function scopeProcessed($query)
    {
        return $query->where('processed', true);
    }

    /**
     * Scope: Unprocessed callbacks
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /**
     * Scope: By lock ID
     */
    public function scopeByLockId($query, string $lockId)
    {
        return $query->where('lock_id', $lockId);
    }

    /**
     * Scope: By event type
     */
    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope: By record type
     */
    public function scopeByRecordType($query, int $recordType)
    {
        return $query->where('record_type', $recordType);
    }

    /**
     * Scope: Recent callbacks
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}
