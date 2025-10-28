@props(['label', 'name', 'value', 'checked' => false, 'disabled' => false])

<div class="flex items-center">
  <input type="radio" name="{{ $name }}" id="{{ $name }}_{{ $value }}" value="{{ $value }}"
    {{ old($name) == $value || $checked ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => 'h-4 w-4 text-blue-600 border-gray-300 transition-all focus:ring-4 focus:ring-blue-500 focus:ring-opacity-20 ' . ($disabled ? 'bg-gray-100 cursor-not-allowed' : 'cursor-pointer')]) }}>

  <label for="{{ $name }}_{{ $value }}"
    class="ml-2 text-sm text-gray-700 {{ $disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}">
    {{ $label }}
  </label>
</div>
