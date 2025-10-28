@props(['active' => false])

<a {{ $attributes->merge(['class' => 'flex items-center px-4 py-3 mb-2 rounded-lg transition-colors duration-200 ' . ($active ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white')]) }}>
    {{ $slot }}
</a>
