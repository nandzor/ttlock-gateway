@props([
    'options' => [5, 10, 20, 50],
    'current' => 10,
    'label' => 'Show:',
    'type' => 'client', // 'client' or 'server'
    'url' => null,
    'param' => 'per_page',
    'size' => 'sm'
])

@php
    // Ensure 10 is in options and set as default if not provided
    if (!in_array(10, $options)) {
        $options = array_merge([10], $options);
        sort($options);
    }
    $current = $current ?: 10;
@endphp

@php
    $isServer = $type === 'server';
    $isClient = $type === 'client';
@endphp

<div class="flex items-center space-x-2">
    @if($label)
        <span class="text-sm text-gray-600">{{ $label }}</span>
    @endif

    @if($isServer)
        {{-- Server-side dropdown --}}
        <div x-data="{
            selected: {{ $current }},
            open: false,
            changePerPage(value) {
                this.selected = value;
                this.open = false;

                const url = new URL(window.location);
                url.searchParams.set('{{ $param }}', value);

                // Preserve all existing parameters
                @if(request()->has('search'))
                    url.searchParams.set('search', '{{ request()->get('search') }}');
                @endif
                @if(request()->has('month'))
                    url.searchParams.set('month', '{{ request()->get('month') }}');
                @endif
                @if(request()->has('branch_id'))
                    url.searchParams.set('branch_id', '{{ request()->get('branch_id') }}');
                @endif
                @if(request()->has('event_type'))
                    url.searchParams.set('event_type', '{{ request()->get('event_type') }}');
                @endif
                @if(request()->has('date_from'))
                    url.searchParams.set('date_from', '{{ request()->get('date_from') }}');
                @endif
                @if(request()->has('date_to'))
                    url.searchParams.set('date_to', '{{ request()->get('date_to') }}');
                @endif

                window.location.href = url.toString();
            }
        }" @click.away="open = false" class="relative inline-block text-left">

            <div>
                <button
                    @click.prevent="open = !open"
                    type="button"
                    class="inline-flex items-center justify-center w-full px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <span x-text="selected + ' per page'"></span>
                    <svg class="w-4 h-4 ml-2 -mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                style="display: none;"
            >
                <div class="py-1">
                    @foreach($options as $value)
                        <button
                            @click.prevent="changePerPage(@js($value))"
                            type="button"
                            class="block w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ $value == $current ? 'bg-blue-50 text-blue-700' : '' }}"
                        >
                            <div class="flex items-center justify-between">
                                <span>{{ $value }}</span>
                                @if($value == $current)
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        {{-- Client-side dropdown --}}
        <div x-data="{
            selected: {{ $current }},
            open: false,
            changePerPage(value) {
                this.selected = value;
                this.open = false;
                this.perPage = value;
                this.goToPage(1);
            }
        }" @click.away="open = false" class="relative inline-block text-left">

            <div>
                <button
                    @click.prevent="open = !open"
                    type="button"
                    class="inline-flex items-center justify-center w-full px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <span x-text="selected + ' per page'"></span>
                    <svg class="w-4 h-4 ml-2 -mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                style="display: none;"
            >
                <div class="py-1">
                    @foreach($options as $value)
                        <button
                            @click.prevent="changePerPage(@js($value))"
                            type="button"
                            class="block w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ $value == $current ? 'bg-blue-50 text-blue-700' : '' }}"
                        >
                            <div class="flex items-center justify-between">
                                <span>{{ $value }}</span>
                                @if($value == $current)
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
