@props([
    'title' => 'Status',
    'status' => 'offline',
    'count' => 0,
    'total' => 0,
    'icon' => 'wifi',
    'lastSeen' => null
])

@php
    $isOnline = $status === 'online';
    $statusColor = $isOnline ? 'green' : 'red';
    $statusText = $isOnline ? 'Online' : 'Offline';
    $statusIcon = $isOnline ? 'check-circle' : 'x-circle';

    // Icon mapping
    $icons = [
        'wifi' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>',
        'lock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>',
        'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'x-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
    ];

    $iconPath = $icons[$icon] ?? $icons['wifi'];
@endphp

<div class="relative overflow-hidden bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-l-4 border-{{ $statusColor }}-500">
    <!-- Status indicator dot -->
    <div class="absolute top-4 right-4">
        <div class="w-3 h-3 bg-{{ $statusColor }}-500 rounded-full animate-pulse"></div>
    </div>

    <div class="relative p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <!-- Icon -->
            <div class="flex-shrink-0">
                <div class="p-3 bg-{{ $statusColor }}-100 rounded-xl">
                    <svg class="w-6 h-6 text-{{ $statusColor }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $iconPath !!}
                    </svg>
                </div>
            </div>

            <!-- Status badge -->
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                    {{ $statusText }}
                </span>
            </div>
        </div>

        <!-- Content -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $title }}</h3>

            @if($total > 0)
                <div class="flex items-center space-x-4 mb-2">
                    <div class="text-sm text-gray-600">
                        <span class="font-medium text-{{ $statusColor }}-600">{{ $count }}</span>
                        <span class="text-gray-500">/ {{ $total }}</span>
                    </div>
                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                        <div class="bg-{{ $statusColor }}-500 h-2 rounded-full transition-all duration-300"
                             style="width: {{ $total > 0 ? ($count / $total) * 100 : 0 }}%"></div>
                    </div>
                </div>
            @else
                <div class="text-sm text-gray-600">
                    <span class="font-medium text-{{ $statusColor }}-600">{{ $count }}</span>
                    <span class="text-gray-500">devices</span>
                </div>
            @endif

            @if($lastSeen)
                <div class="text-xs text-gray-500 mt-2">
                    Last seen: {{ \Carbon\Carbon::parse($lastSeen)->diffForHumans() }}
                </div>
            @endif
        </div>
    </div>
</div>
