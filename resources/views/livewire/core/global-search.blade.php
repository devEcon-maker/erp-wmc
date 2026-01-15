<div class="relative w-full"
    x-data="{
        open: @entangle('showDropdown'),
        selectedIndex: -1,
        items: [],
        init() {
            this.$watch('open', (value) => {
                if (value) {
                    this.selectedIndex = -1;
                    this.$nextTick(() => {
                        this.items = Array.from(this.$refs.results?.querySelectorAll('[data-search-item]') || []);
                    });
                }
            });
        },
        navigate(direction) {
            if (!this.open) return;

            if (direction === 'down') {
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.items.length - 1);
            } else {
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
            }

            if (this.selectedIndex >= 0 && this.items[this.selectedIndex]) {
                this.items[this.selectedIndex].scrollIntoView({ block: 'nearest' });
            }
        },
        selectCurrent() {
            if (this.selectedIndex >= 0 && this.items[this.selectedIndex]) {
                window.location.href = this.items[this.selectedIndex].dataset.url;
            }
        }
    }"
    @keydown.escape="open = false; $wire.closeDropdown()"
    @keydown.arrow-down.prevent="navigate('down')"
    @keydown.arrow-up.prevent="navigate('up')"
    @keydown.enter.prevent="selectCurrent()"
    @click.outside="open = false; $wire.closeDropdown()">

    <div class="relative w-full group">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary group-focus-within:text-primary transition-colors material-symbols-outlined">search</span>
        <input
            type="text"
            wire:model.live.debounce.300ms="query"
            @focus="$wire.openDropdown()"
            class="w-full bg-surface-dark border-none rounded-xl py-2.5 pl-10 pr-10 text-sm text-white placeholder-text-secondary focus:ring-2 focus:ring-primary focus:bg-surface-highlight transition-all outline-none"
            placeholder="Rechercher (contacts, produits, factures...)">
        @if($query)
            <button wire:click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary hover:text-white">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        @endif
    </div>

    <!-- Search Results Dropdown -->
    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        x-ref="results"
        class="absolute top-full left-0 right-0 mt-2 bg-surface-dark border border-[#3a2e24] rounded-xl shadow-xl z-50 max-h-[70vh] overflow-y-auto"
        style="display: none;">

        @if(count($results) > 0)
            @foreach($results as $categoryKey => $category)
                <div class="border-b border-[#3a2e24] last:border-b-0">
                    <div class="px-4 py-2 bg-surface-highlight">
                        <div class="flex items-center gap-2 text-text-secondary text-xs font-semibold uppercase">
                            <span class="material-symbols-outlined text-[16px]">{{ $category['icon'] }}</span>
                            {{ $category['label'] }}
                        </div>
                    </div>
                    <div class="p-2">
                        @foreach($category['items'] as $index => $item)
                            <a href="{{ $item['url'] }}"
                                data-search-item
                                data-url="{{ $item['url'] }}"
                                class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-highlight transition-colors"
                                :class="{ 'bg-surface-highlight': selectedIndex === {{ $loop->parent->index * 5 + $index }} }">
                                <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-primary text-[18px]">{{ $item['icon'] }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white truncate">{{ $item['label'] }}</p>
                                    <p class="text-xs text-text-secondary truncate">{{ $item['subtitle'] }}</p>
                                </div>
                                <span class="material-symbols-outlined text-text-secondary text-[18px]">chevron_right</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @elseif(strlen($query) >= 2)
            <div class="p-8 text-center">
                <span class="material-symbols-outlined text-4xl text-text-secondary opacity-50 mb-2">search_off</span>
                <p class="text-text-secondary">Aucun resultat pour "{{ $query }}"</p>
            </div>
        @endif
    </div>
</div>
