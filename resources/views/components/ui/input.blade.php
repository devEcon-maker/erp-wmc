@props(['disabled' => false, 'readonly' => false, 'label' => null, 'error' => null, 'icon' => null, 'required' => false])

<label class="flex flex-col w-full mb-4">
    @if ($label)
        <p class="text-gray-900 dark:text-white text-base font-medium leading-normal pb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </p>
    @endif

    <div class="flex w-full flex-1 items-stretch rounded-lg shadow-sm {{ $error ? 'border-red-500' : '' }}">
        <input {{ $disabled ? 'disabled' : '' }} {{ $readonly ? 'readonly' : '' }} {!! $attributes->merge([
    'class' => 'form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden text-gray-900 dark:text-white focus:outline-0 focus:ring-0 border border-gray-300 dark:border-border-dark bg-white dark:bg-input-dark focus:border-primary dark:focus:border-primary h-14 placeholder:text-gray-400 dark:placeholder:text-text-muted p-[15px] text-base font-normal leading-normal transition-colors ' .
        ($icon ? 'rounded-l-lg border-r-0' : 'rounded-lg') .
        ($error ? ' border-red-500 focus:border-red-500' : '') .
        (($disabled || $readonly) ? ' opacity-60 cursor-not-allowed' : '')
]) !!}>

        @if($icon)
            <div
                class="text-gray-400 dark:text-text-muted flex border border-gray-300 dark:border-border-dark bg-white dark:bg-input-dark items-center justify-center pr-[15px] rounded-r-lg border-l-0">
                <span class="material-symbols-outlined">{{ $icon }}</span>
            </div>
        @endif
    </div>

    @if ($error)
        <p class="mt-2 text-sm text-red-500">{{ $error }}</p>
    @endif
</label>