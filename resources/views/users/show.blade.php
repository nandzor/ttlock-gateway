@extends('layouts.app')

@section('title', 'View User')
@section('page-title', 'User Details')

@section('content')
  <div class="max-w-2xl">
    <x-card :padding="false">
      <!-- Header with Gradient -->
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8">
        <div class="flex items-center">
          <div class="h-20 w-20 rounded-full bg-white flex items-center justify-center shadow-lg">
            <span class="text-3xl font-bold text-blue-600">{{ substr($user->name, 0, 1) }}</span>
          </div>
          <div class="ml-6 flex-1">
            <h2 class="text-2xl font-bold text-white mb-1">{{ $user->name }}</h2>
            <p class="text-blue-100 flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              {{ $user->email }}
            </p>
          </div>
          <x-badge :variant="$user->isAdmin() ? 'purple' : 'primary'" size="lg">
            {{ ucfirst($user->role) }}
          </x-badge>
        </div>
      </div>

      <!-- Details Section -->
      <div class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- User ID -->
          <div class="p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg mr-3">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                </svg>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-0.5">User ID</label>
                <p class="text-sm font-semibold text-gray-900">#{{ $user->id }}</p>
              </div>
            </div>
          </div>

          <!-- Member Since -->
          <div class="p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 rounded-lg mr-3">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-0.5">Member Since</label>
                <p class="text-sm font-semibold text-gray-900">{{ $user->created_at->format('F d, Y') }}</p>
              </div>
            </div>
          </div>

          <!-- Email Verified -->
          <div class="p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <div class="p-2 bg-purple-100 rounded-lg mr-3">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-0.5">Email Status</label>
                <div class="flex items-center">
                  @if ($user->email_verified_at)
                    <x-badge variant="success" size="sm">Verified</x-badge>
                  @else
                    <x-badge variant="warning" size="sm">Not Verified</x-badge>
                  @endif
                </div>
              </div>
            </div>
          </div>

          <!-- Last Updated -->
          <div class="p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <div class="p-2 bg-yellow-100 rounded-lg mr-3">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-0.5">Last Updated</label>
                <p class="text-sm font-semibold text-gray-900">{{ $user->updated_at->format('M d, Y - h:i A') }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Account Stats -->
        <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
          <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Account Information
          </h4>
          <div class="grid grid-cols-3 gap-4">
            <div class="text-center">
              <p class="text-2xl font-bold text-blue-600">{{ $user->id }}</p>
              <p class="text-xs text-gray-600 mt-1">User ID</p>
            </div>
            <div class="text-center">
              @php
                $daysAgo = $daysSinceCreated;
              @endphp
              <p class="text-2xl font-bold text-green-600">
                @if ($daysAgo === 0)
                  Today
                @elseif ($daysAgo === 1)
                  Yesterday
                @else
                  {{ $daysAgo }} days ago
                @endif
              </p>
              <p class="text-xs text-gray-600 mt-1">Joined</p>
            </div>
            <div class="text-center">
              <p class="text-2xl font-bold text-purple-600">{{ $user->isAdmin() ? 'Admin' : 'User' }}</p>
              <p class="text-xs text-gray-600 mt-1">Account Type</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
          <x-button variant="secondary" :href="route('users.index')">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to List
          </x-button>
          <div class="flex space-x-3">
            @if ($user->id !== auth()->id())
              <x-button variant="danger" @click="confirmDelete({{ $user->id }})">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete
              </x-button>
            @endif
            <x-button variant="primary" :href="route('users.edit', $user->id)">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit User
            </x-button>
          </div>
        </div>
      </div>
    </x-card>
  </div>

  <!-- Delete Confirmation Modal -->
  @if ($user->id !== auth()->id())
    <!-- Hidden delete form -->
    <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST"
      class="hidden">
      @csrf
      @method('DELETE')
    </form>

    <x-confirm-modal id="confirm-delete" title="Confirm Delete"
      message="This action cannot be undone. The user will be permanently deleted." confirmText="Delete User"
      cancelText="Cancel" icon="warning" confirmAction="handleDeleteConfirm(data)" />
  @endif

  @push('scripts')
    <script>
      // Store userId for deletion
      let pendingDeleteUserId = {{ $user->id }};

      function confirmDelete(userId) {
        pendingDeleteUserId = userId;
        console.log('confirmDelete called with userId:', userId);
        // Dispatch event to open modal with userId
        window.dispatchEvent(new CustomEvent('open-modal-confirm-delete', {
          detail: {
            userId: userId
          }
        }));
      }

      function handleDeleteConfirm(data) {
        const userId = data?.userId || pendingDeleteUserId;
        console.log('handleDeleteConfirm called with userId:', userId);
        if (userId) {
          const form = document.getElementById('delete-form-' + userId);
          if (form) {
            form.submit();
          }
        }
      }

      // Make functions globally available
      window.confirmDelete = confirmDelete;
      window.handleDeleteConfirm = handleDeleteConfirm;
    </script>
  @endpush
@endsection
