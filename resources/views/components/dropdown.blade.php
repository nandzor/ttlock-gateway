@props([
    'align' => 'right',
    'width' => '48',
    'trigger' => null,
])

@php
  $alignmentClasses = match ($align) {
      'left' => 'origin-top-left left-0',
      'top' => 'origin-top',
      'right' => 'origin-top-right right-0',
      default => 'origin-top-right right-0',
  };

  $widthClasses = match ($width) {
      '48' => 'w-48',
      '56' => 'w-56',
      '64' => 'w-64',
      default => 'w-48',
  };
@endphp

<div x-data="{ open: false }" @click.away="open = false" class="relative inline-block text-left">
  <div @click="open = !open">
    @if ($trigger)
      {{ $trigger }}
    @else
      <button type="button"
        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
        Options
        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
            clip-rule="evenodd" />
        </svg>
      </button>
    @endif
  </div>

  <div x-show="open" x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
    x-transition:leave-end="transform opacity-0 scale-95"
    class="absolute z-50 mt-2 {{ $widthClasses }} rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 {{ $alignmentClasses }}"
    style="display: none;" @click="open = false">
    <div class="py-1">
      {{ $slot }}
    </div>
  </div>
</div>
