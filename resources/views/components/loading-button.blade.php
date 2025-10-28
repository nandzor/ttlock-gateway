@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'loading' => false,
    'loadingText' => 'Loading...',
    'icon' => null,
    'href' => null,
])

@php
  $baseClasses =
      'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

  $variants = [
      'primary' =>
          'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white shadow-lg shadow-blue-500/50 focus:ring-blue-500',
      'secondary' =>
          'bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white shadow-lg shadow-gray-500/50 focus:ring-gray-500',
      'success' =>
          'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white shadow-lg shadow-green-500/50 focus:ring-green-500',
      'danger' =>
          'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white shadow-lg shadow-red-500/50 focus:ring-red-500',
      'warning' =>
          'bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white shadow-lg shadow-yellow-500/50 focus:ring-yellow-500',
      'purple' =>
          'bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white shadow-lg shadow-purple-500/50 focus:ring-purple-500',
      'outline' => 'border-2 border-gray-300 hover:border-gray-400 text-gray-700 hover:bg-gray-50 focus:ring-gray-500',
  ];

  $sizes = [
      'sm' => 'px-3 py-1.5 text-sm',
      'md' => 'px-4 py-2 text-sm',
      'lg' => 'px-5 py-2.5 text-base',
  ];

  $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

@if ($href)
  <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if ($loading)
      <x-spinner size="sm" color="white" class="mr-2" />
      {{ $loadingText }}
    @else
      @if ($icon)
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          {!! $icon !!}
        </svg>
      @endif
      {{ $slot }}
    @endif
  </a>
@else
  <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}
    @if ($loading) disabled @endif>
    @if ($loading)
      <x-spinner size="sm" color="white" class="mr-2" />
      {{ $loadingText }}
    @else
      @if ($icon)
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          {!! $icon !!}
        </svg>
      @endif
      {{ $slot }}
    @endif
  </button>
@endif
