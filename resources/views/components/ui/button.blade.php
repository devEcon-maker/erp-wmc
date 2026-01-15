@props([
    'type' => 'primary',
    'size' => 'md',
    'icon' => null,
    'href' => null,
    'submit' => false,
])
@php
    $baseClasses = 'inline-flex items-center justify-center font-bold rounded-xl shadow-sm focus:outline-none transition-all duration-200';

    $typeClasses = match ($type) {
        'primary' => 'text-white bg-primary hover:bg-primary/90 shadow-lg shadow-primary/20',
        'secondary' => 'border border-[#3a2e24] text-text-secondary bg-transparent hover:bg-surface-highlight hover:text-white',
        'danger' => 'text-red-400 border border-red-500/20 hover:bg-red-500/10 hover:text-red-300',
        'success' => 'text-green-400 border border-green-500/20 hover:bg-green-500/10 hover:text-green-300',
        'warning' => 'text-yellow-400 border border-yellow-500/20 hover:bg-yellow-500/10 hover:text-yellow-300',
        default => 'border border-[#3a2e24] text-text-secondary bg-surface-dark hover:text-white',
    };

    $sizeClasses = match ($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-6 py-3 text-base',
        default => 'px-5 py-2.5 text-sm',
    };

    $classes = $baseClasses . ' ' . $typeClasses . ' ' . $sizeClasses;
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon) 
            @if(str_starts_with($icon, 'fa-'))
                <i class="{{ $icon }} mr-2"></i> 
            @else
                <span class="material-symbols-outlined mr-2 text-[20px]">{{ $icon }}</span>
            @endif
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $submit ? 'submit' : 'button' }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            @if(str_starts_with($icon, 'fa-'))
                <i class="{{ $icon }} mr-2"></i>
            @else
                <span class="material-symbols-outlined mr-2 text-[20px]">{{ $icon }}</span>
            @endif
        @endif
        {{ $slot }}
    </button>
@endif
