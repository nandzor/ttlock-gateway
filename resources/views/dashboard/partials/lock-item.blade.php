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

<div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
  <div class="flex items-center justify-between">
    <div class="flex-1">
      @if($hasAlias)
        <h5 class="font-medium text-gray-900">{{ $lock['lockAlias'] }}</h5>
        <p class="text-xs text-gray-500 italic">{{ $lock['lockName'] ?? 'Unknown Lock' }}</p>
      @else
        <h5 class="font-medium text-gray-900">{{ $lock['lockName'] ?? 'Unknown Lock' }}</h5>
      @endif
      
      <p class="text-xs text-gray-500 mt-1">
        ID: {{ $lock['lockId'] ?? 'Unknown' }} | MAC: {{ $lock['lockMac'] ?? 'Unknown' }}
      </p>
      
      @if($rssi !== null)
        <p class="text-xs text-gray-500 mt-1">
          Signal: <span class="font-medium text-{{ $rssiColor }}-600">{{ $rssi }} dBm</span> 
          <span class="text-{{ $rssiColor }}-600">({{ ucfirst($rssiStatus) }})</span>
        </p>
      @endif
      
      @if($updateDate)
        <p class="text-xs text-gray-500 mt-1">Last update: {{ $updateDate->diffForHumans() }}</p>
      @endif
    </div>
  </div>
</div>
