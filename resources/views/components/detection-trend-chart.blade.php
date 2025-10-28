@props([
    'data' => [],
    'title' => 'Detection Trend',
    'height' => 'h-64',
])

@php
  $trendData = collect($data);
  $maxCount = $trendData->max('count') ?: 1;
@endphp

<x-card :title="$title" {{ $attributes }}>
  @if ($trendData->isEmpty())
    <div class="{{ $height }} flex flex-col items-center justify-center text-gray-400">
      <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
      </svg>
      <p class="font-medium">No detection data available</p>
      <p class="text-sm mt-1">No detections found for the selected period</p>
    </div>
  @else
    <div class="{{ $height }} flex items-end justify-between gap-1 px-2">
      @foreach ($trendData as $trend)
        @php
          $count = $trend->count ?? 0;
          $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
          $barHeight = $count > 0 ? max(8, $percentage) : 2;
        @endphp
        <div class="flex-1 flex flex-col items-center group">
          <div class="w-full flex flex-col items-center justify-end" style="height: 200px;">
            @if ($count > 0)
              <div
                class="w-full bg-gradient-to-t from-blue-600 to-blue-400 rounded-t hover:from-blue-700 hover:to-blue-500 transition-all duration-200 cursor-pointer shadow-sm group-hover:shadow-md relative"
                style="height: {{ $barHeight }}%;"
                title="{{ $count }} detections on {{ \Carbon\Carbon::parse($trend->date)->format('M d, Y') }}">
                <span
                  class="absolute -top-5 left-1/2 transform -translate-x-1/2 text-xs font-semibold text-gray-700 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                  {{ $count }}
                </span>
              </div>
            @else
              <div class="w-full bg-gray-200 rounded-t" style="height: 4px;"
                title="No detections on {{ \Carbon\Carbon::parse($trend->date)->format('M d, Y') }}">
              </div>
            @endif
          </div>
          <div class="mt-2 text-center">
            <span class="block text-xs text-gray-500 font-medium">
              {{ \Carbon\Carbon::parse($trend->date)->format('D') }}
            </span>
            <span class="block text-xs text-gray-600 font-semibold">
              {{ \Carbon\Carbon::parse($trend->date)->format('M d') }}
            </span>
            <span class="block text-sm font-bold {{ $count > 0 ? 'text-blue-600' : 'text-gray-400' }} mt-1">
              {{ $count }}
            </span>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</x-card>
