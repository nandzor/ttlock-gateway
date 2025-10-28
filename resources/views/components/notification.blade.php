@props([
    'type' => 'success',
    'message' => '',
    'duration' => 3000,
    'position' => 'top-right',
])

@php
  $types = [
      'success' => 'bg-green-500 text-white',
      'error' => 'bg-red-500 text-white',
      'warning' => 'bg-yellow-500 text-white',
      'info' => 'bg-blue-500 text-white',
  ];

  $positions = [
      'top-right' => 'top-4 right-4',
      'top-left' => 'top-4 left-4',
      'bottom-right' => 'bottom-4 right-4',
      'bottom-left' => 'bottom-4 left-4',
      'top-center' => 'top-4 left-1/2 transform -translate-x-1/2',
      'bottom-center' => 'bottom-4 left-1/2 transform -translate-x-1/2',
  ];

  $icons = [
      'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />',
      'error' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />',
      'warning' =>
          '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
      'info' =>
          '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
  ];
@endphp

<div id="notification-{{ uniqid() }}"
  class="fixed {{ $positions[$position] }} {{ $types[$type] }} px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full opacity-0"
  data-duration="{{ $duration }}" style="display: none;">
  <div class="flex items-center">
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      {!! $icons[$type] !!}
    </svg>
    <span class="font-medium">{{ $message }}</span>
    <button onclick="closeNotification(this)" class="ml-4 text-white/80 hover:text-white">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>
</div>

<script>
  function showNotification(message, type = 'success', duration = 3000, position = 'top-right') {
    const notification = document.createElement('div');
    const bgColors = {
      'success': 'bg-green-500',
      'error': 'bg-red-500',
      'warning': 'bg-yellow-500',
      'info': 'bg-blue-500'
    };
    const positions = {
      'top-right': 'top-4 right-4',
      'top-left': 'top-4 left-4',
      'bottom-right': 'bottom-4 right-4',
      'bottom-left': 'bottom-4 left-4',
      'top-center': 'top-4 left-1/2 transform -translate-x-1/2',
      'bottom-center': 'bottom-4 left-1/2 transform -translate-x-1/2'
    };
    const icons = {
      'success': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />',
      'error': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />',
      'warning': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
      'info': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
    };

    notification.className =
      `fixed ${positions[position]} ${bgColors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full`;
    notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${icons[type]}
                </svg>
                <span class="font-medium">${message}</span>
                <button onclick="closeNotification(this)" class="ml-4 text-white/80 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
      notification.classList.remove('translate-x-full');
    }, 100);

    // Auto close
    setTimeout(() => {
      closeNotification(notification.querySelector('button'));
    }, duration);
  }

  function closeNotification(button) {
    const notification = button.closest('.fixed');
    notification.classList.add('translate-x-full');
    setTimeout(() => notification.remove(), 300);
  }
</script>
