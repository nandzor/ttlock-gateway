@props([
    'type' => 'client', // 'client' or 'server'
    'paginator' => null,
    'size' => 'sm'
])

@php
    $isServer = $type === 'server';
    $isClient = $type === 'client';
@endphp

@if($isServer && $paginator && $paginator->hasPages())
    {{-- Server-side pagination buttons --}}
    <div class="flex items-center space-x-2">
        <!-- Previous button -->
        @if($paginator->onFirstPage())
            <button
                type="button"
                disabled
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-md opacity-50 cursor-not-allowed"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Previous
            </button>
        @else
            <a
                href="{{ $paginator->previousPageUrl() }}"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Previous
            </a>
        @endif

        <!-- Page numbers -->
        @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if($page == $paginator->currentPage())
                <button
                    type="button"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium bg-blue-600 text-white border border-blue-600 rounded-md"
                >
                    {{ $page }}
                </button>
            @else
                <a
                    href="{{ $url }}"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    {{ $page }}
                </a>
            @endif
        @endforeach

        <!-- Next button -->
        @if($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Next
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @else
            <button
                type="button"
                disabled
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-md opacity-50 cursor-not-allowed"
            >
                Next
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        @endif
    </div>
@elseif($isClient)
    {{-- Client-side pagination buttons --}}
    <div class="flex items-center space-x-2" x-show="totalPages > 1">
        <!-- Previous button -->
        <button
            type="button"
            class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''"
        >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Previous
        </button>

        <!-- Page numbers -->
        <template x-for="page in visiblePages" :key="page">
            <button
                type="button"
                class="inline-flex items-center px-3 py-2 text-sm font-medium border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                :class="page === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-700 bg-white hover:bg-gray-50'"
                @click="goToPage(page)"
            >
                <span x-text="page"></span>
            </button>
        </template>

        <!-- Next button -->
        <button
            type="button"
            class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''"
        >
            Next
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
@endif
