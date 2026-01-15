@props(['name', 'show' => false, 'maxWidth' => '2xl'])

@php
    $maxWidth = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ][$maxWidth];
@endphp

@if($show)
<div x-data="{ show: true }"
    x-init="$nextTick(() => show = true)"
    x-on:close-modal.window="show = false"
    x-show="show"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
    {{-- Backdrop --}}
    <div x-show="show" class="fixed inset-0 transform transition-all" x-on:click="show = false; $wire.set('confirmingDeletion', false)">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    </div>

    {{-- Content --}}
    <div x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative mb-6 bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto">
        {{ $slot }}
    </div>
</div>
@endif