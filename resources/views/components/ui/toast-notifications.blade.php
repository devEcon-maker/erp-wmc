<div id="toast-container"
    x-data="{
        notifications: [],
        add(type, message) {
            const id = Date.now();
            this.notifications.push({ id, type, message });
            setTimeout(() => this.remove(id), 5000);
        },
        remove(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        }
    }"
    @notify.window="add($event.detail.type, $event.detail.message)"
    class="fixed top-4 right-4 z-[100] space-y-3 w-96 max-w-[calc(100vw-2rem)]">

    <script>
        document.addEventListener('livewire:init', () => {
            // Écouter l'événement 'notify'
            Livewire.on('notify', (params) => {
                const data = Array.isArray(params) ? params[0] : params;
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type: data.type, message: data.message }
                }));
            });

            // Écouter aussi l'événement 'toast' (alias de notify)
            Livewire.on('toast', (params) => {
                const data = Array.isArray(params) ? params[0] : params;
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type: data.type, message: data.message }
                }));
            });
        });
    </script>

    {{-- Flash Messages from Session --}}
    @if(session('success'))
        <div x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 5000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="flex items-start gap-3 p-4 bg-green-500/20 border border-green-500/30 rounded-xl backdrop-blur-sm shadow-lg">
            <span class="material-symbols-outlined text-green-400 flex-shrink-0">check_circle</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-green-400">Succes</p>
                <p class="text-sm text-green-300/80 mt-0.5">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-green-400/60 hover:text-green-400 flex-shrink-0">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 8000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="flex items-start gap-3 p-4 bg-red-500/20 border border-red-500/30 rounded-xl backdrop-blur-sm shadow-lg">
            <span class="material-symbols-outlined text-red-400 flex-shrink-0">error</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-red-400">Erreur</p>
                <p class="text-sm text-red-300/80 mt-0.5">{{ session('error') }}</p>
            </div>
            <button @click="show = false" class="text-red-400/60 hover:text-red-400 flex-shrink-0">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 6000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="flex items-start gap-3 p-4 bg-yellow-500/20 border border-yellow-500/30 rounded-xl backdrop-blur-sm shadow-lg">
            <span class="material-symbols-outlined text-yellow-400 flex-shrink-0">warning</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-yellow-400">Attention</p>
                <p class="text-sm text-yellow-300/80 mt-0.5">{{ session('warning') }}</p>
            </div>
            <button @click="show = false" class="text-yellow-400/60 hover:text-yellow-400 flex-shrink-0">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
    @endif

    @if(session('info'))
        <div x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 5000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="flex items-start gap-3 p-4 bg-blue-500/20 border border-blue-500/30 rounded-xl backdrop-blur-sm shadow-lg">
            <span class="material-symbols-outlined text-blue-400 flex-shrink-0">info</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-blue-400">Information</p>
                <p class="text-sm text-blue-300/80 mt-0.5">{{ session('info') }}</p>
            </div>
            <button @click="show = false" class="text-blue-400/60 hover:text-blue-400 flex-shrink-0">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
    @endif

    {{-- Dynamic Notifications via Alpine --}}
    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            :class="{
                'bg-green-500/20 border-green-500/30': notification.type === 'success',
                'bg-red-500/20 border-red-500/30': notification.type === 'error',
                'bg-yellow-500/20 border-yellow-500/30': notification.type === 'warning',
                'bg-blue-500/20 border-blue-500/30': notification.type === 'info'
            }"
            class="flex items-start gap-3 p-4 border rounded-xl backdrop-blur-sm shadow-lg">
            <span class="material-symbols-outlined flex-shrink-0"
                :class="{
                    'text-green-400': notification.type === 'success',
                    'text-red-400': notification.type === 'error',
                    'text-yellow-400': notification.type === 'warning',
                    'text-blue-400': notification.type === 'info'
                }"
                x-text="notification.type === 'success' ? 'check_circle' : (notification.type === 'error' ? 'error' : (notification.type === 'warning' ? 'warning' : 'info'))">
            </span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium"
                    :class="{
                        'text-green-400': notification.type === 'success',
                        'text-red-400': notification.type === 'error',
                        'text-yellow-400': notification.type === 'warning',
                        'text-blue-400': notification.type === 'info'
                    }"
                    x-text="notification.type === 'success' ? 'Succes' : (notification.type === 'error' ? 'Erreur' : (notification.type === 'warning' ? 'Attention' : 'Information'))">
                </p>
                <p class="text-sm mt-0.5"
                    :class="{
                        'text-green-300/80': notification.type === 'success',
                        'text-red-300/80': notification.type === 'error',
                        'text-yellow-300/80': notification.type === 'warning',
                        'text-blue-300/80': notification.type === 'info'
                    }"
                    x-text="notification.message">
                </p>
            </div>
            <button @click="remove(notification.id)" class="flex-shrink-0"
                :class="{
                    'text-green-400/60 hover:text-green-400': notification.type === 'success',
                    'text-red-400/60 hover:text-red-400': notification.type === 'error',
                    'text-yellow-400/60 hover:text-yellow-400': notification.type === 'warning',
                    'text-blue-400/60 hover:text-blue-400': notification.type === 'info'
                }">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
    </template>
</div>
