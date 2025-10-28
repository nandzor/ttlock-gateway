@props(['paginator'])

@if ($paginator->hasPages())
  <nav class="flex items-center justify-center space-x-2">
    {{-- Previous Button --}}
    @if ($paginator->onFirstPage())
      <span
        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Previous
      </span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}"
        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 hover:text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Previous
      </a>
    @endif

    {{-- Page Numbers --}}
    <div class="hidden sm:flex items-center space-x-1">
      @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        // Smart pagination logic - never show more than 7 page numbers
        $showEllipsis = $lastPage > 7; // Show ellipsis if more than 7 pages

        if ($showEllipsis) {
            // Calculate start and end based on current page position
            if ($currentPage <= 4) {
                // Near the beginning: show 1,2,3,4,5,...,last
                $start = 1;
                $end = 5;
                $showStartEllipsis = false;
                $showEndEllipsis = true;
            } elseif ($currentPage >= $lastPage - 3) {
                // Near the end: show 1,...,last-4,last-3,last-2,last-1,last
                $start = $lastPage - 4;
                $end = $lastPage;
                $showStartEllipsis = true;
                $showEndEllipsis = false;
            } else {
                // In the middle: show 1,...,current-1,current,current+1,...,last
                $start = $currentPage - 1;
                $end = $currentPage + 1;
                $showStartEllipsis = true;
                $showEndEllipsis = true;
            }
        } else {
            // Show all pages if 7 or fewer
            $start = 1;
            $end = $lastPage;
            $showStartEllipsis = false;
            $showEndEllipsis = false;
        }
      @endphp

      {{-- First page with ellipsis --}}
      @if ($showStartEllipsis)
        <a href="{{ $paginator->url(1) }}"
          class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 hover:text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
          1
        </a>
        <span class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-500">
          ...
        </span>
      @elseif ($start > 1)
        <a href="{{ $paginator->url(1) }}"
          class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 hover:text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
          1
        </a>
      @endif

      {{-- Page range --}}
      @for ($page = $start; $page <= $end; $page++)
        @if ($page == $currentPage)
          <span
            class="inline-flex items-center justify-center w-10 h-10 text-sm font-semibold text-white bg-blue-600 border border-blue-600 rounded-lg shadow-sm">
            {{ $page }}
          </span>
        @else
          <a href="{{ $paginator->url($page) }}"
            class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 hover:text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            {{ $page }}
          </a>
        @endif
      @endfor

      {{-- Last page with ellipsis --}}
      @if ($showEndEllipsis)
        <span class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-500">
          ...
        </span>
        <a href="{{ $paginator->url($lastPage) }}"
          class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 hover:text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
          {{ $lastPage }}
        </a>
      @elseif ($end < $lastPage)
        <a href="{{ $paginator->url($lastPage) }}"
          class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 hover:text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
          {{ $lastPage }}
        </a>
      @endif
    </div>

    {{-- Mobile page indicator --}}
    <div class="sm:hidden flex items-center space-x-2">
      <span class="text-sm text-gray-700">
        Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
      </span>
    </div>

    {{-- Next Button --}}
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}"
        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 hover:text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        Next
        <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </a>
    @else
      <span
        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
        Next
        <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </span>
    @endif
  </nav>
@endif
