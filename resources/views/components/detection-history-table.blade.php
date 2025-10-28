@props([
    'detections' => [],
    'showJsonData' => true,
    'showBranchName' => true,
    'showDeviceName' => true,
    'showTimestamp' => true,
    'emptyMessage' => 'No detections found'
])

@php
    $colspan = ($showTimestamp ? 1 : 0) + ($showBranchName ? 1 : 0) + ($showDeviceName ? 1 : 0) + ($showJsonData ? 1 : 0);
@endphp

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                @if($showTimestamp)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                @endif
                @if($showBranchName)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                @endif
                @if($showDeviceName)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                @endif
                @if($showJsonData)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                @endif
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <template x-for="detection in paginatedItems" :key="detection.id">
                <tr class="hover:bg-gray-50">
                    @if($showTimestamp)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatTime(detection.detection_timestamp)"></td>
                    @endif
                    @if($showBranchName)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="detection.branch_name || 'N/A'"></td>
                    @endif
                    @if($showDeviceName)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="detection.device_name || 'N/A'"></td>
                    @endif
                    @if($showJsonData)
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <template x-if="detection.detection_data">
                                <div x-data="{ showJson: false }">
                                    <x-button size="sm" variant="primary" @click="showJson = !showJson">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        <span x-text="showJson ? 'Hide JSON' : 'View JSON'"></span>
                                    </x-button>
                                    <div x-show="showJson" x-cloak x-transition class="mt-3">
                                        <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs overflow-x-auto max-w-full">
                                            <pre x-text="JSON.stringify(detection.detection_data, null, 2)"></pre>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!detection.detection_data">
                                <span class="text-gray-400 text-xs">No data</span>
                            </template>
                        </td>
                    @endif
                </tr>
            </template>

            <!-- Empty state -->
            <template x-if="isEmpty">
                <x-empty-state
                    message="{{ $emptyMessage }}"
                    icon="search"
                    colspan="{{ $colspan }}"
                />
            </template>
        </tbody>
    </table>
</div>
