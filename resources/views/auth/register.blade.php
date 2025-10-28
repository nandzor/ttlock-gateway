@extends('layouts.guest')

@section('title', 'Register')

@section('content')
  <div class="bg-white rounded-2xl shadow-2xl p-8">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Create Account</h1>
      <p class="text-gray-600 mt-2">Sign up to get started</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
      @csrf

      <div class="mb-6">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
        @error('name')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
        @error('email')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
        <input id="password" type="password" name="password" required
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror">
        @error('password')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
      </div>

      <button type="submit"
        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium">
        Sign Up
      </button>
    </form>

    <div class="mt-6 text-center">
      <p class="text-sm text-gray-600">
        Already have an account?
        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-medium">Sign in</a>
      </p>
    </div>
  </div>
@endsection
