@props([
    'items' => [],
    'perPageOptions' => [5, 10, 20, 50],
    'defaultPerPage' => 10,
    'showPerPageSelector' => true,
    'showPaginationInfo' => true,
    'maxVisiblePages' => 5,
    'itemName' => 'items',
    'emptyMessage' => 'No items found',
    'scrollToTop' => true,
    'storageKey' => 'client_pagination_per_page'
])

<div x-data="clientPagination({
    items: @js($items),
    perPageOptions: @js($perPageOptions),
    defaultPerPage: {{ $defaultPerPage }},
    maxVisiblePages: {{ $maxVisiblePages }},
    itemName: '{{ $itemName }}',
    emptyMessage: '{{ $emptyMessage }}',
    scrollToTop: {{ $scrollToTop ? 'true' : 'false' }},
    storageKey: '{{ $storageKey }}'
})">
    <!-- Pagination Controls - Top -->
    @if($showPerPageSelector || $showPaginationInfo)
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end space-y-3 sm:space-y-0">
                @if($showPerPageSelector)
                    <!-- Items per page selector -->
                    <x-per-page-selector
                        :options="$perPageOptions"
                        :current="$defaultPerPage"
                        type="client"
                    />
                @endif

                {{-- @if($showPaginationInfo)
                    <!-- Pagination info -->
                    <x-pagination-info type="client" />
                @endif --}}
            </div>
        </div>
    @endif

    <!-- Content Slot -->
    <div>
        {{ $slot }}
    </div>

    <!-- Pagination Controls - Bottom -->
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end space-y-3 sm:space-y-0">
            @if($showPaginationInfo)
                <!-- Pagination info -->
                <x-pagination-info type="client" />
            @endif

            <!-- Pagination buttons -->
            <x-pagination-buttons type="client" />
        </div>
    </div>
</div>
