<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">Notifications</h2>
            <p class="text-text-secondary text-sm">Toutes vos notifications.</p>
        </div>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <button wire:click="markAllAsRead" class="px-4 py-2 bg-primary/20 text-primary rounded-lg hover:bg-primary/30">
                Tout marquer comme lu
            </button>
        @endif
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-4">
        <div class="flex rounded-lg bg-surface-dark border border-[#3a2e24] p-1">
            <button wire:click="$set('filter', 'all')"
                class="px-4 py-2 text-sm rounded-lg transition-colors {{ $filter === 'all' ? 'bg-primary text-white' : 'text-text-secondary hover:text-white' }}">
                Toutes
            </button>
            <button wire:click="$set('filter', 'unread')"
                class="px-4 py-2 text-sm rounded-lg transition-colors {{ $filter === 'unread' ? 'bg-primary text-white' : 'text-text-secondary hover:text-white' }}">
                Non lues
            </button>
            <button wire:click="$set('filter', 'read')"
                class="px-4 py-2 text-sm rounded-lg transition-colors {{ $filter === 'read' ? 'bg-primary text-white' : 'text-text-secondary hover:text-white' }}">
                Lues
            </button>
        </div>

        <select wire:model.live="type" class="px-4 py-2 bg-surface-dark border border-[#3a2e24] rounded-lg text-white text-sm">
            <option value="">Tous les types</option>
            <option value="LeaveRequest">Conges</option>
            <option value="ExpenseReport">Notes de frais</option>
            <option value="Invoice">Factures</option>
            <option value="StockAlert">Alertes stock</option>
            <option value="Task">Taches</option>
            <option value="Event">Evenements</option>
        </select>
    </div>

    <!-- Notifications List -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl overflow-hidden">
        @forelse($notifications as $notification)
            <div wire:key="notification-{{ $notification->id }}"
                class="p-4 border-b border-[#3a2e24] hover:bg-surface-highlight transition-colors {{ !$notification->read_at ? 'bg-primary/5' : '' }}">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                        {{ $notification->data['color'] ?? 'bg-primary/20' }}">
                        <span class="material-symbols-outlined {{ $notification->data['icon_color'] ?? 'text-primary' }}">
                            {{ $notification->data['icon'] ?? 'notifications' }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <p class="text-white {{ !$notification->read_at ? 'font-medium' : '' }}">
                                    {{ $notification->data['message'] ?? 'Notification' }}
                                </p>
                                @if(isset($notification->data['subtitle']))
                                    <p class="text-sm text-text-secondary mt-1">{{ $notification->data['subtitle'] }}</p>
                                @endif
                                <p class="text-xs text-text-secondary mt-2">{{ $notification->created_at->translatedFormat('d M Y H:i') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if(isset($notification->data['url']))
                                    <a href="{{ $notification->data['url'] }}" class="p-2 text-text-secondary hover:text-primary rounded-lg hover:bg-surface-highlight">
                                        <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                                    </a>
                                @endif
                                @if(!$notification->read_at)
                                    <button wire:click="markAsRead('{{ $notification->id }}')" class="p-2 text-text-secondary hover:text-green-400 rounded-lg hover:bg-surface-highlight">
                                        <span class="material-symbols-outlined text-[18px]">done</span>
                                    </button>
                                @endif
                                <button wire:click="deleteNotification('{{ $notification->id }}')" wire:confirm="Supprimer cette notification ?"
                                    class="p-2 text-text-secondary hover:text-red-400 rounded-lg hover:bg-surface-highlight">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <span class="material-symbols-outlined text-5xl text-text-secondary opacity-50 mb-4">notifications_off</span>
                <p class="text-text-secondary">Aucune notification</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
