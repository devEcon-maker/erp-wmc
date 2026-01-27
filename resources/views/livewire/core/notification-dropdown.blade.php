<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button @click="open = !open" class="relative p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg transition-colors">
        <span class="material-symbols-outlined">notifications</span>
        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 min-w-[18px] h-[18px] bg-red-500 rounded-full text-[10px] font-bold text-white flex items-center justify-center px-1">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute right-0 mt-2 w-80 bg-surface-dark border border-[#3a2e24] rounded-xl shadow-xl z-50"
        style="display: none;">

        <div class="p-4 border-b border-[#3a2e24] flex justify-between items-center">
            <h3 class="font-semibold text-white">Notifications</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-primary hover:text-primary/80">
                    Tout marquer comme lu
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div wire:key="notification-{{ $notification->id }}"
                    class="p-4 border-b border-[#3a2e24] hover:bg-surface-highlight transition-colors {{ !$notification->read_at ? 'bg-primary/5' : '' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                            {{ $notification->data['color'] ?? 'bg-primary/20' }}">
                            <span class="material-symbols-outlined text-[18px] {{ $notification->data['icon_color'] ?? 'text-primary' }}">
                                {{ $notification->data['icon'] ?? 'notifications' }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-white">{{ $notification->data['message'] ?? 'Notification' }}</p>
                            <p class="text-xs text-text-secondary mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        @if(!$notification->read_at)
                            <button wire:click="markAsRead('{{ $notification->id }}')" class="text-text-secondary hover:text-white">
                                <span class="material-symbols-outlined text-[18px]">done</span>
                            </button>
                        @endif
                    </div>
                    @if(isset($notification->data['url']))
                        <a href="{{ $notification->data['url'] }}" class="block mt-2 text-xs text-primary hover:text-primary/80">
                            Voir les details
                        </a>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-text-secondary">
                    <span class="material-symbols-outlined text-3xl opacity-50 mb-2">notifications_off</span>
                    <p class="text-sm">Aucune notification</p>
                </div>
            @endforelse
        </div>

        <div class="p-3 border-t border-[#3a2e24]">
            <a href="{{ route('notifications.index') }}" class="block text-center text-sm text-primary hover:text-primary/80">
                Voir toutes les notifications
            </a>
        </div>
    </div>
</div>
