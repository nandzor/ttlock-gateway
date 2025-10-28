@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
  <div class="max-w-2xl">
    <x-card title="Update User Information">
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">Modify the user details below</p>
          <x-badge :variant="$user->isAdmin() ? 'purple' : 'primary'">
            {{ ucfirst($user->role) }}
          </x-badge>
        </div>
      </div>

      <form method="POST" action="{{ route('users.update', $user->id) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <x-input name="name" label="Full Name" :value="$user->name" placeholder="Enter full name" required />

        <x-input type="email" name="email" label="Email Address" :value="$user->email" placeholder="user@example.com"
          required hint="This email will be used for login (no spaces allowed)"
          onkeypress="return event.charCode != 32" />

        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
          <div class="flex">
            <svg class="w-5 h-5 text-yellow-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="text-sm font-medium text-yellow-800">Password Update</p>
              <p class="text-xs text-yellow-700 mt-1">Leave password fields empty to keep the current password</p>
            </div>
          </div>
        </div>

        <x-input type="password" name="password" label="New Password" placeholder="Leave blank to keep current"
          hint="Password must be at least 6 characters" />

        <x-input type="password" name="password_confirmation" label="Confirm New Password"
          placeholder="Re-enter new password" />

        <x-select name="role" label="User Role" :options="['user' => 'Regular User', 'admin' => 'Administrator']" :selected="$user->role" required
          hint="Choose the appropriate role for this user" />

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
          <x-button variant="secondary" :href="route('users.index')">
            Cancel
          </x-button>
          <x-button type="submit" variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Update User
          </x-button>
        </div>
      </form>
    </x-card>
  </div>
@endsection
