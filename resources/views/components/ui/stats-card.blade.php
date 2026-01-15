@props(['title', 'value', 'icon' => null, 'color' => 'orange'])

<div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                @if ($icon)
                    <i class="{{ $icon }} text-{{ $color }}-600 text-3xl"></i>
                @endif
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                        {{ $title }}
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                        {{ $value }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>