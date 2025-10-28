<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Login') - {{ config('app.name') }}</title>

  <!-- Google Fonts - Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
  class="bg-gradient-to-br from-gray-900 via-blue-900 to-gray-900 min-h-screen flex items-center justify-center relative overflow-hidden">
  <!-- Background Pattern -->
  <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60"
    xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%239C92AC"
    fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="2" /%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20">
  </div>

  <!-- Floating Elements -->
  <div class="absolute top-20 left-20 w-32 h-32 bg-blue-500/10 rounded-full blur-xl animate-pulse"></div>
  <div class="absolute bottom-20 right-20 w-48 h-48 bg-purple-500/10 rounded-full blur-xl animate-pulse delay-1000">
  </div>
  <div class="absolute top-1/2 left-1/4 w-24 h-24 bg-indigo-500/10 rounded-full blur-lg animate-pulse delay-500"></div>

  <div class="relative z-10 w-full max-w-md px-6">
    @yield('content')
  </div>

  <!-- Include notification component for global showNotification function -->
  <x-notification />
</body>

</html>
