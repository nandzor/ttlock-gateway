@props([
    'name' => 'is_active',
    'value' => '',
    'required' => false,
    'placeholder' => 'Select Status',
    'label' => null,
    'hint' => null,
    'showAllOption' => true,
    'allOptionText' => 'All Status',
    'options' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
])

@if ($label)
  <div class="space-y-1.5">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">
      {{ $label }}
      @if ($required)
        <span class="text-red-500 ml-0.5">*</span>
      @endif
    </label>

    <select name="{{ $name }}" id="{{ $name }}" {{ $required ? 'required' : '' }}
      {{ $attributes->merge(['class' => 'block w-full px-3 py-2 text-sm rounded-lg border-gray-300 shadow-sm transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500 focus:ring-opacity-20']) }}>

      @if ($showAllOption)
        <option value="">{{ $allOptionText }}</option>
      @endif

      @foreach ($options as $optionValue => $optionLabel)
        <option value="{{ $optionValue }}" {{ $value == $optionValue ? 'selected' : '' }}>
          {{ $optionLabel }}
        </option>
      @endforeach
    </select>

    @if ($hint)
      <p class="text-sm text-gray-600 mt-1">{{ $hint }}</p>
    @endif
  </div>
@else
  <select name="{{ $name }}" id="{{ $name }}" {{ $required ? 'required' : '' }}
    {{ $attributes->merge(['class' => 'block w-full px-3 py-2 text-sm rounded-lg border-gray-300 shadow-sm transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500 focus:ring-opacity-20']) }}>

    @if ($showAllOption)
      <option value="">{{ $allOptionText }}</option>
    @endif

    @foreach ($options as $optionValue => $optionLabel)
      <option value="{{ $optionValue }}" {{ $value == $optionValue ? 'selected' : '' }}>
        {{ $optionLabel }}
      </option>
    @endforeach
  </select>
@endif
