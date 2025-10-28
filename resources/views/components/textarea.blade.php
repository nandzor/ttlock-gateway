@props([
    'label' => null,
    'name',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'rows' => 4,
    'error' => null,
    'hint' => null,
])

<div class="space-y-1.5">
  @if ($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">
      {{ $label }}
      @if ($required)
        <span class="text-red-500 ml-0.5">*</span>
      @endif
    </label>
  @endif

  <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }} {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => 'block w-full px-3 py-2 text-sm rounded-lg border-gray-300 shadow-sm transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500 focus:ring-opacity-20 ' . ($error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : '') . ($disabled ? ' bg-gray-100 cursor-not-allowed' : '')]) }}>{{ old($name, $value) }}</textarea>

  @if ($hint && !$error)
    <p class="text-sm text-gray-600 mt-1">{{ $hint }}</p>
  @endif

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
