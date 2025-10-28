@props([
  'title',
  'value',
  'icon',
  'color' => 'blue',
  'trend' => null,
  'trendUp' => true
])

@php
$colors = [
  'blue' => 'from-blue-500 to-blue-600',
  'green' => 'from-green-500 to-green-600',
  'purple' => 'from-purple-500 to-purple-600',
  'orange' => 'from-orange-500 to-orange-600',
  'red' => 'from-red-500 to-red-600',
  'indigo' => 'from-indigo-500 to-indigo-600',
  'yellow' => 'from-yellow-500 to-yellow-600',
];

$iconBg = [
  'blue' => 'bg-blue-100',
  'green' => 'bg-green-100',
  'purple' => 'bg-purple-100',
  'orange' => 'bg-orange-100',
  'red' => 'bg-red-100',
  'indigo' => 'bg-indigo-100',
  'yellow' => 'bg-yellow-100',
];
@endphp

<div class="relative overflow-hidden bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
  <!-- Gradient accent in top-right corner -->
  <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br {{ $colors[$color] }} opacity-5 rounded-bl-full"></div>

  <div class="relative p-6">
    <!-- Top row: Icon and Trend -->
    <div class="flex items-center justify-between mb-4">
      <!-- Icon -->
      <div class="flex-shrink-0">
        <div class="p-3 {{ $iconBg[$color] }} rounded-xl">
          <svg class="w-6 h-6 text-{{ $color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $icon !!}
          </svg>
        </div>
      </div>

      <!-- Trend indicator -->
      @if($trend)
        <div class="flex items-center space-x-1 {{ $trendUp ? 'text-green-600' : 'text-red-600' }}">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            @if($trendUp)
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            @else
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            @endif
          </svg>
          <span class="text-sm font-semibold">{{ $trend }}</span>
        </div>
      @endif
    </div>

    <!-- Content -->
    <div>
      <p class="text-sm font-medium text-gray-600 mb-1">{{ $title }}</p>
      <h3 class="text-3xl font-bold text-gray-900">{{ $value }}</h3>
    </div>
  </div>
</div>
