@props([
    'type' => 'client', // 'client' or 'server'
    'paginator' => null,
    'itemName' => 'items',
    'size' => 'sm'
])

@php
    $isServer = $type === 'server';
    $isClient = $type === 'client';
@endphp

<div class="text-sm text-gray-700">
    @if($isServer && $paginator)
        {{-- Server-side pagination info --}}
        Showing
        <span class="font-medium">{{ $paginator->firstItem() ?? 0 }}</span>
        to
        <span class="font-medium">{{ $paginator->lastItem() ?? 0 }}</span>
        of
        <span class="font-medium">{{ $paginator->total() }}</span>
        {{ $itemName }}
    @else
        {{-- Client-side pagination info --}}
        Showing
        <span class="font-medium" x-text="startItem"></span>
        to
        <span class="font-medium" x-text="endItem"></span>
        of
        <span class="font-medium" x-text="totalItems"></span>
        <span x-text="itemName"></span>
    @endif
</div>
