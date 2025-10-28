@props([
    'label' => null,
    'name',
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'error' => null,
])

<div class="space-y-2">
  <div class="flex items-center">
    <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}"
      {{ old($name, $checked) ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }}
      {{ $attributes->merge(['class' => 'h-4 w-4 text-blue-600 border-gray-300 rounded transition-all focus:ring-4 focus:ring-blue-500 focus:ring-opacity-20 ' . ($disabled ? 'bg-gray-100 cursor-not-allowed' : 'cursor-pointer')]) }}>

    @if ($label)
      <label for="{{ $name }}"
        class="ml-2 text-sm text-gray-700 {{ $disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}">
        {{ $label }}
      </label>
    @endif
  </div>

  @if ($error)
    <p class="text-sm text-red-600 flex items-center mt-1">
      <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      {{ $error }}
    </p>
  @endif

  @error($name)
    <p class="text-sm text-red-600 flex items-center mt-1">
      <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      {{ $message }}
    </p>
  @enderror
</div>
