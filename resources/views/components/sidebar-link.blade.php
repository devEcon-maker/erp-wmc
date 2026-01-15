@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'flex items-center px-4 py-3 bg-gray-800 text-white border-l-4 border-orange-500 transition duration-150 ease-in-out'
        : 'flex items-center px-4 py-3 text-gray-400 hover:bg-gray-800 hover:text-white transition duration-150 ease-in-out border-l-4 border-transparent';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>