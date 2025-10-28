@props([
  'type' => 'info',
  'dismissible' => true
])

@php
$types = [
  'success' => 'bg-gradient-to-r from-green-50 to-green-100 border-green-500 text-green-900',
  'error' => 'bg-gradient-to-r from-red-50 to-red-100 border-red-500 text-red-900',
  'warning' => 'bg-gradient-to-r from-yellow-50 to-yellow-100 border-yellow-500 text-yellow-900',
  'info' => 'bg-gradient-to-r from-blue-50 to-blue-100 border-blue-500 text-blue-900',
];

$icons = [
  'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
  'error' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
  'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
  'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start p-4 border-l-4 rounded-lg shadow-md ' . $types[$type]]) }} role="alert" x-data="{ show: true }" x-show="show" x-transition>
  <svg class="flex-shrink-0 w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    {!! $icons[$type] !!}
  </svg>
  
  <div class="flex-1">
    {{ $slot }}
  </div>
  
  @if($dismissible)
    <button @click="show = false" class="flex-shrink-0 ml-4 text-current hover:opacity-70 transition-opacity">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  @endif
</div>

