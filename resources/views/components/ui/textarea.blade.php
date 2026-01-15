@props(['disabled' => false, 'label' => null, 'error' => null])

<label class="flex flex-col w-full mb-4">
    @if ($label)
        <p class="text-gray-900 dark:text-white text-base font-medium leading-normal pb-2">{{ $label }}</p>
    @endif

    <div class="flex w-full flex-1 items-stretch rounded-lg shadow-sm">
        <textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' => 'form-textarea flex w-full min-w-0 flex-1 resize-y overflow-hidden rounded-lg text-gray-900 dark:text-white focus:outline-0 focus:ring-0 border border-gray-300 dark:border-border-dark bg-white dark:bg-input-dark focus:border-primary dark:focus:border-primary p-[15px] text-base font-normal leading-normal transition-colors ' .
        ($error ? ' border-red-500 focus:border-red-500' : '')
]) !!}>{{ $slot }}</textarea>
    </div>

    @if ($error)
        <p class="mt-2 text-sm text-red-500">{{ $error }}</p>
    @endif
</label>