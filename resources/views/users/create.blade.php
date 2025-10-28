@extends('layouts.app')

@section('title', 'Create User')
@section('page-title', 'Create New User')

@section('content')
  <div class="max-w-2xl">
    <x-card title="User Information">
      <div class="mb-6">
        <p class="text-sm text-gray-500">Fill in the details to create a new user account</p>
      </div>

      <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
        @csrf

        <x-input name="name" label="Full Name" placeholder="Enter full name" required />

        <x-input type="email" name="email" label="Email Address" placeholder="user@example.com" required
          hint="This email will be used for login (no spaces allowed)" onkeypress="return event.charCode != 32" />

        <x-input type="password" name="password" label="Password" placeholder="Enter password" required
          hint="Password must be at least 6 characters" />

        <x-input type="password" name="password_confirmation" label="Confirm Password" placeholder="Re-enter password"
          required />

        <x-select name="role" label="User Role" :options="['user' => 'Regular User', 'admin' => 'Administrator']" placeholder="Select a role" required
          hint="Choose the appropriate role for this user" />

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
          <x-button variant="secondary" :href="route('users.index')">
            Cancel
          </x-button>
          <x-button type="submit" variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Create User
          </x-button>
        </div>
      </form>
    </x-card>
  </div>
@endsection
