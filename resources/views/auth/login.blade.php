@extends('layouts.guest')

@section('title', 'Login')

@section('content')
  <div class="w-full max-w-md">
    <!-- Header Section -->
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-white mb-2">Welcome Back</h1>
      <p class="text-gray-300">Sign in to TTLock Gateway</p>
    </div>

    <!-- Main Card -->
    <x-card class="backdrop-blur-sm bg-white/95 shadow-2xl border border-white/20">
      <!-- Demo Credentials Section -->
      <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl">
        <div class="flex items-center mb-3">
          <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm font-semibold text-blue-900">Demo Credentials</p>
        </div>
        <div class="flex items-center justify-between">
          <div class="text-sm text-blue-800">
            <p class="font-mono bg-blue-100 px-2 py-1 rounded">admin@cctv.com</p>
            <p class="font-mono bg-blue-100 px-2 py-1 rounded mt-1">admin123</p>
          </div>
          <x-button type="button" variant="primary" size="sm" onclick="fillDemoCredentials()" class="shadow-md">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Use Demo
          </x-button>
        </div>
      </div>

      <!-- Login Form -->
      <x-form-wrapper method="POST" action="{{ route('login') }}" class="space-y-6">

        <!-- Email Input -->
        <x-input label="Email Address" name="email" type="email" :value="old('email')" placeholder="Enter your email"
          required autofocus
          icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207' />" />

        <!-- Password Input -->
        <x-input label="Password" name="password" type="password" placeholder="Enter your password" required
          icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' />" />

        <!-- Remember Me -->
        <x-checkbox name="remember" label="Remember me for 30 days" />

        <!-- Submit Button -->
        <x-button type="submit" variant="primary" size="lg" class="w-full shadow-lg">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
          </svg>
          Sign In
        </x-button>
      </x-form-wrapper>

      <!-- Footer Links -->
      <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="text-center">
          <p class="text-sm text-gray-600">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700 transition-colors">
              Create account
            </a>
          </p>
        </div>
      </div>
    </x-card>

    <!-- Additional Info -->
    <div class="mt-6 text-center">
      <p class="text-xs text-gray-400">
        Secure access to your CCTV monitoring system
      </p>
    </div>
  </div>

  <script>
    function fillDemoCredentials() {
      document.getElementById('email').value = 'admin@cctv.com';
      document.getElementById('password').value = 'admin123';

      // Show success notification using global function
      showNotification('Demo credentials filled!', 'success');
    }
  </script>
@endsection
