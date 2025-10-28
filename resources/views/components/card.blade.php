@props(['title' => null, 'padding' => true])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-md overflow-visible']) }}>
  @if ($title)
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
    </div>
  @endif

  <div class="{{ $padding ? 'p-6' : '' }}">
    {{ $slot }}
  </div>
</div>
