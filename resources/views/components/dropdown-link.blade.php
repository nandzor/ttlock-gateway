@props([
    'href' => '#',
    'icon' => null,
    'variant' => 'default',
])

@php
  $variantClasses = match ($variant) {
      'danger' => 'text-red-700 hover:bg-red-50 hover:text-red-900',
      'success' => 'text-green-700 hover:bg-green-50 hover:text-green-900',
      'warning' => 'text-yellow-700 hover:bg-yellow-50 hover:text-yellow-900',
      default => 'text-gray-700 hover:bg-gray-50 hover:text-gray-900',
  };
@endphp

<a href="{{ $href }}"
  {{ $attributes->merge(['class' => 'group flex items-center px-4 py-2 text-sm transition-colors ' . $variantClasses]) }}>
  @if ($icon)
    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      {!! $icon !!}
    </svg>
  @endif
  {{ $slot }}
</a>
