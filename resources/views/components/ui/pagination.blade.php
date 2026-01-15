@props(['paginator'])

@if ($paginator->hasPages())
    <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
        {{ $paginator->links() }}
    </div>
@endif