@props([
    'label' => null,
    'name',
    'checked' => false,
    'disabled' => false,
    'size' => 'md',
])

@php
  $sizes = [
      'sm' => 'w-9 h-5',
      'md' => 'w-11 h-6',
      'lg' => 'w-14 h-7',
  ];

  $dotSizes = [
      'sm' => 'h-4 w-4',
      'md' => 'h-5 w-5',
      'lg' => 'h-6 w-6',
  ];
@endphp

<div class="flex items-center justify-between">
  @if ($label)
    <label for="{{ $name }}" class="text-sm font-medium text-gray-700 {{ $disabled ? 'opacity-50' : '' }}">
      {{ $label }}
    </label>
  @endif

  <button type="button" role="switch" aria-checked="{{ old($name, $checked) ? 'true' : 'false' }}"
    {{ $disabled ? 'disabled' : '' }}
    class="relative inline-flex {{ $sizes[$size] }} flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-4 focus:ring-blue-500 focus:ring-opacity-20 {{ old($name, $checked) ? 'bg-blue-600' : 'bg-gray-200' }} {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
    onclick="toggleSwitch(this, '{{ $name }}')">
    <span class="sr-only">{{ $label }}</span>
    <span
      class="pointer-events-none inline-block {{ $dotSizes[$size] }} transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ old($name, $checked) ? 'translate-x-5' : 'translate-x-0' }}"></span>
  </button>

  <input type="hidden" name="{{ $name }}" id="{{ $name }}"
    value="{{ old($name, $checked) ? '1' : '0' }}">
</div>

@once
  @push('scripts')
    <script>
      function toggleSwitch(button, name) {
        const isChecked = button.getAttribute('aria-checked') === 'true';
        const newState = !isChecked;

        button.setAttribute('aria-checked', newState);
        button.classList.toggle('bg-blue-600', newState);
        button.classList.toggle('bg-gray-200', !newState);

        const span = button.querySelector('span:last-child');
        span.classList.toggle('translate-x-5', newState);
        span.classList.toggle('translate-x-0', !newState);

        const input = document.getElementById(name);
        input.value = newState ? '1' : '0';
      }
    </script>
  @endpush
@endonce
