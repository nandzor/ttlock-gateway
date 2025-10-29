<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>

  <!-- Google Fonts - Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
    /* Professional Sidebar Scrollbar */
    #sidebar-nav::-webkit-scrollbar {
      width: 6px;
    }

    #sidebar-nav::-webkit-scrollbar-track {
      background: rgba(31, 41, 55, 0.3);
      border-radius: 10px;
    }

    #sidebar-nav::-webkit-scrollbar-thumb {
      background: rgba(75, 85, 99, 0.5);
      border-radius: 10px;
      transition: background 0.2s ease;
    }

    #sidebar-nav::-webkit-scrollbar-thumb:hover {
      background: rgba(107, 114, 128, 0.7);
    }

    /* Firefox */
    #sidebar-nav {
      scrollbar-width: thin;
      scrollbar-color: rgba(75, 85, 99, 0.5) rgba(31, 41, 55, 0.3);
    }

    /* Smooth scrolling */
    #sidebar-nav {
      scroll-behavior: smooth;
    }
  </style>
</head>

<body class="bg-gray-50">
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside id="sidebar"
      class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 -translate-x-full">
      <div class="flex items-center justify-center h-16 bg-gray-800">
        <h1 class="text-xl font-bold">{{ config('app.name') }}</h1>
      </div>

      <nav id="sidebar-nav" class="mt-8 px-4 space-y-1 overflow-y-auto pr-2" style="max-height: calc(100vh - 240px);">
        <!-- Dashboard -->
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
          <x-sidebar-icon name="dashboard" />
          Dashboard
        </x-sidebar-link>



        <!-- User Management Section -->
        <x-sidebar-section title="User Management" />

        <!-- Users -->
        <x-sidebar-link :href="route('users.index')" :active="request()->routeIs('users.*')">
          <x-sidebar-icon name="users" />
          Users
        </x-sidebar-link>

        <!-- TTLock -->
        <x-sidebar-section title="TTLock" />
        <x-sidebar-link :href="route('ttlock.callback.histories.index')" :active="request()->routeIs('ttlock.callback.histories.*')">
          <x-sidebar-icon name="history" />
          Callback Histories
        </x-sidebar-link>
      </nav>

      <div class="absolute bottom-0 w-64 px-4 py-4 border-t border-gray-800">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center">
              <span class="text-sm font-medium">{{ substr(auth()->user()->name, 0, 1) }}</span>
            </div>
          </div>
          <div class="ml-3 flex-1">
            <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
            <p class="text-xs text-gray-400">{{ auth()->user()->email }}</p>
          </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
          @csrf
          <button type="submit"
            class="w-full flex items-center justify-center px-4 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-gray-800 rounded-lg">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Logout
          </button>
        </form>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Navigation -->
      <header class="flex items-center justify-between px-6 py-4 bg-white shadow-md">
        <div class="flex items-center">
          <button id="sidebarToggle" class="text-gray-500 focus:outline-none lg:hidden">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <h2 class="ml-4 text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
        </div>
        <div class="flex items-center space-x-4">
          <span
            class="px-3 py-1 text-xs font-semibold rounded-full {{ auth()->user()->isAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
            {{ ucfirst(auth()->user()->role) }}
          </span>
        </div>
      </header>

      <!-- Page Content -->
      <main id="mainContent"
        class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-gray-50 to-gray-100 p-6">
        @if (session('success'))
          <div data-toast-success="{{ session('success') }}"></div>
        @endif

        @if (session('error'))
          <div data-toast-error="{{ session('error') }}"></div>
        @endif

        @yield('content')
      </main>
    </div>
  </div>

  @stack('scripts')

  <script>
    (() => {
      'use strict';

      const SidebarScroll = {
        config: {
          navId: 'sidebar-nav',
          activeClass: 'bg-gray-800',
          scrollDelay: 100,
          mobileScrollDelay: 320,
          visibilityBuffer: 80
        },

        /**
         * Check if element is visible in scrollable container
         */
        isElementVisible(element, container) {
          const containerTop = container.scrollTop;
          const containerBottom = containerTop + container.clientHeight;
          const elementTop = element.offsetTop;
          const elementBottom = elementTop + element.offsetHeight;

          return (
            elementTop >= containerTop + this.config.visibilityBuffer &&
            elementBottom <= containerBottom - this.config.visibilityBuffer
          );
        },

        /**
         * Scroll to active menu item
         */
        scrollToActive(delay = this.config.scrollDelay) {
          const nav = document.getElementById(this.config.navId);
          const activeLink = nav?.querySelector(`a.${this.config.activeClass}`);

          if (!activeLink || !nav) return;

          setTimeout(() => {
            // Only scroll if not already visible
            if (!this.isElementVisible(activeLink, nav)) {
              activeLink.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest'
              });
            }
          }, delay);
        },

        /**
         * Initialize sidebar scroll
         */
        init() {
          // Auto-scroll on page load
          this.scrollToActive();
        }
      };

      const SidebarToggle = {
        /**
         * Initialize sidebar toggle
         */
        init() {
          const toggle = document.getElementById('sidebarToggle');
          const sidebar = document.getElementById('sidebar');

          if (!toggle || !sidebar) return;

          toggle.addEventListener('click', () => {
            const isOpening = sidebar.classList.contains('-translate-x-full');
            sidebar.classList.toggle('-translate-x-full');

            // Scroll to active when opening sidebar
            if (isOpening) {
              SidebarScroll.scrollToActive(SidebarScroll.config.mobileScrollDelay);
            }
          });
        }
      };

      document.addEventListener('DOMContentLoaded', () => {
        SidebarScroll.init();
        SidebarToggle.init();
      });

    })();
  </script>
</body>

</html>
