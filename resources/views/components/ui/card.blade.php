@props(['title' => null, 'footer' => null])

<div {{ $attributes->merge(['class' => 'bg-surface-dark border border-[#3a2e24] shadow-sm rounded-2xl overflow-hidden']) }}>
    @if ($title)
        <div class="px-6 py-4 border-b border-[#3a2e24]">
            <h3 class="text-lg leading-6 font-bold text-white">
                {{ $title }}
            </h3>
        </div>
    @endif

    <div class="px-6 py-6">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="px-6 py-4 bg-background-dark/50 border-t border-[#3a2e24] text-right">
            {{ $footer }}
        </div>
    @endif
</div>