@php
  // Helper function to calculate RSSI status
  $rssi = $lock['rssi'] ?? null;
  $rssiStatus = 'unknown';
  $rssiColor = 'gray';
  
  if ($rssi !== null) {
    if ($rssi > -75) {
      $rssiStatus = 'strong';
      $rssiColor = 'green';
    } elseif ($rssi >= -85 && $rssi <= -75) {
      $rssiStatus = 'medium';
      $rssiColor = 'yellow';
    } elseif ($rssi < -85) {
      $rssiStatus = 'weak';
      $rssiColor = 'red';
    }
  }
  
  $updateDate = isset($lock['updateDate']) ? \Carbon\Carbon::createFromTimestamp($lock['updateDate'] / 1000) : null;
  $hasAlias = isset($lock['lockAlias']) && $lock['lockAlias'] != $lock['lockName'];
@endphp

<div class="px-3 py-2 bg-white border-b border-gray-100 hover:bg-gray-50 transition-colors last:border-b-0">
  <div class="flex items-center justify-between gap-3">
    <div class="flex-1 min-w-0">
      <!-- Baris 1: Lock Name -->
      <div class="flex items-center gap-2 mb-1">
        @if($hasAlias)
          <h5 class="font-semibold text-sm text-gray-900 truncate">{{ $lock['lockAlias'] }}</h5>
          <span class="text-xs text-gray-400 italic flex-shrink-0">{{ $lock['lockName'] ?? 'Unknown' }}</span>
        @else
          <h5 class="font-semibold text-sm text-gray-900 truncate">{{ $lock['lockName'] ?? 'Unknown Lock' }}</h5>
        @endif
      </div>
      
      <!-- Baris 2: Details -->
      <div class="flex items-center gap-2 text-xs text-gray-500">
        <span class="font-mono text-gray-600">{{ $lock['lockId'] ?? 'Unknown' }}</span>
        <span class="text-gray-300">•</span>
        <span class="font-mono">{{ $lock['lockMac'] ?? 'Unknown' }}</span>
        @if($rssi !== null)
          <span class="text-gray-300">•</span>
          <span class="font-medium text-{{ $rssiColor }}-600">{{ $rssi }} dBm</span>
        @endif
        @if($updateDate)
          <span class="text-gray-300">•</span>
          <span class="text-gray-400">{{ $updateDate->diffForHumans() }}</span>
        @endif
      </div>
    </div>
  </div>
</div>
