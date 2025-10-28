@props([
  'label' => null,
  'name',
  'options' => [], // ['VAL' => 'Label'] or ['Label1', 'Label2']
  'selected' => '',
  'placeholder' => 'Ketik untuk mencari atau tambah baru...',
  'required' => false,
  'disabled' => false,
  'hint' => null,
  'maxSuggestions' => 8,
])

@php
  $initial = old($name, $selected ?? '');
  // Flatten options to an array of display strings (unique)
  $displayOptions = [];
  foreach (($options ?? []) as $val => $text) {
      $displayOptions[] = is_int($val) ? (string) $text : (string) $text;
  }
  $displayOptions = array_values(array_unique(array_filter($displayOptions, fn($v) => $v !== '')));
@endphp

<div class="space-y-1.5">
  @if ($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">
      {{ $label }}
      @if ($required)
        <span class="text-red-500 ml-0.5">*</span>
      @endif
    </label>
  @endif

  <div x-data="{
        query: @js((string) $initial),
        open: false,
        activeIndex: -1,
        all: @js($displayOptions),
        max: @js((int) $maxSuggestions),
        get filtered() {
          const q = (this.query || '').toLowerCase();
          if (!q) return [];
          return this.all.filter(o => o && o.toLowerCase().includes(q)).slice(0, this.max);
        },
        choose(v){ this.query = v; this.open = false; this.activeIndex = -1; },
      }" class="relative">

    <x-input
      name="{{ $name }}"
      :label="null"
      :value="$initial"
      :placeholder="$placeholder"
      :required="$required"
      :disabled="$disabled"
      x-model="query"
      @input="open = true"
      @focus="open = filtered.length > 0"
      @keydown.arrow-down.prevent="if(activeIndex < filtered.length-1){ activeIndex++ } open=true"
      @keydown.arrow-up.prevent="if(activeIndex > 0){ activeIndex-- } open=true"
      @keydown.enter.prevent="if(filtered[activeIndex]) choose(filtered[activeIndex])"
      @blur="setTimeout(()=>open=false,120)"
    />

    <!-- Suggestions dropdown -->
    <div x-show="open && filtered.length" x-cloak
      class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg">
      <ul class="max-h-56 overflow-auto py-1">
        <template x-for="(item, idx) in filtered" :key="item">
          <li>
            <button type="button"
              class="w-full text-left px-3 py-2 text-sm hover:bg-blue-50"
              :class="{ 'bg-blue-100': idx === activeIndex }"
              @mouseenter="activeIndex = idx"
              @mousedown.prevent="choose(item)">
              <span x-text="item"></span>
            </button>
          </li>
        </template>
      </ul>
    </div>
  </div>

  @if ($hint)
    <p class="text-sm text-gray-600 mt-1">{{ $hint }}</p>
  @endif
</div>


