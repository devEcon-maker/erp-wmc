<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestion des Rôles</h1>
            <p class="text-text-secondary text-sm">Configurez les rôles et leurs permissions</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.users.index') }}" class="bg-surface-dark hover:bg-surface-highlight text-white font-bold py-2 px-4 rounded-xl flex items-center gap-2 transition-colors border border-[#3a2e24]">
                <span class="material-symbols-outlined text-[20px]">group</span>
                Utilisateurs
            </a>
            <button wire:click="createRole" class="bg-primary hover:bg-primary/90 text-white font-bold py-2 px-4 rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Nouveau Rôle
            </button>
        </div>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
            @php
                $colors = [
                    'super_admin' => ['bg' => 'bg-red-500/10', 'border' => 'border-red-500/30', 'text' => 'text-red-400', 'icon' => 'shield'],
                    'admin' => ['bg' => 'bg-purple-500/10', 'border' => 'border-purple-500/30', 'text' => 'text-purple-400', 'icon' => 'admin_panel_settings'],
                    'commercial' => ['bg' => 'bg-blue-500/10', 'border' => 'border-blue-500/30', 'text' => 'text-blue-400', 'icon' => 'storefront'],
                    'comptable' => ['bg' => 'bg-green-500/10', 'border' => 'border-green-500/30', 'text' => 'text-green-400', 'icon' => 'account_balance'],
                    'rh' => ['bg' => 'bg-yellow-500/10', 'border' => 'border-yellow-500/30', 'text' => 'text-yellow-400', 'icon' => 'groups'],
                    'manager' => ['bg' => 'bg-orange-500/10', 'border' => 'border-orange-500/30', 'text' => 'text-orange-400', 'icon' => 'supervisor_account'],
                    'employe' => ['bg' => 'bg-gray-500/10', 'border' => 'border-gray-500/30', 'text' => 'text-gray-400', 'icon' => 'person'],
                ];
                $color = $colors[$role->name] ?? ['bg' => 'bg-primary/10', 'border' => 'border-primary/30', 'text' => 'text-primary', 'icon' => 'badge'];
                $isSystemRole = in_array($role->name, ['super_admin', 'admin', 'employe']);
            @endphp
            <div class="bg-surface-dark border {{ $color['border'] }} rounded-2xl overflow-hidden hover:shadow-lg transition-shadow">
                <!-- Role Header -->
                <div class="{{ $color['bg'] }} p-4 border-b {{ $color['border'] }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl {{ $color['bg'] }} flex items-center justify-center">
                                <span class="material-symbols-outlined {{ $color['text'] }} text-2xl">{{ $color['icon'] }}</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</h3>
                                @if($isSystemRole)
                                    <span class="text-xs text-text-secondary">Rôle système</span>
                                @endif
                            </div>
                        </div>
                        @if(!$isSystemRole || $role->name !== 'super_admin')
                            <div class="flex items-center gap-1">
                                <button
                                    wire:click="editRole({{ $role->id }})"
                                    class="p-2 text-text-secondary hover:text-white hover:bg-white/10 rounded-lg transition-colors"
                                    title="Modifier"
                                >
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                @if(!$isSystemRole)
                                    <button
                                        wire:click="deleteRole({{ $role->id }})"
                                        wire:confirm="Êtes-vous sûr de vouloir supprimer ce rôle ?"
                                        class="p-2 text-text-secondary hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
                                        title="Supprimer"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Role Stats -->
                <div class="p-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-text-secondary">
                            <span class="material-symbols-outlined text-[18px]">key</span>
                            <span class="text-sm">Permissions</span>
                        </div>
                        <span class="text-white font-bold">{{ $role->permissions_count }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-text-secondary">
                            <span class="material-symbols-outlined text-[18px]">group</span>
                            <span class="text-sm">Utilisateurs</span>
                        </div>
                        <span class="text-white font-bold">{{ $role->users_count }}</span>
                    </div>

                    <!-- Permissions Preview -->
                    @if($role->permissions->count() > 0)
                        <div class="pt-3 border-t border-[#3a2e24]">
                            <p class="text-xs text-text-secondary mb-2">Permissions :</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach($role->permissions->take(5) as $permission)
                                    <span class="text-xs px-2 py-0.5 rounded bg-background-dark text-text-secondary">
                                        {{ $permission->name }}
                                    </span>
                                @endforeach
                                @if($role->permissions->count() > 5)
                                    <span class="text-xs px-2 py-0.5 rounded bg-primary/20 text-primary">
                                        +{{ $role->permissions->count() - 5 }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Create/Edit Role -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm overflow-y-auto">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl w-full max-w-2xl shadow-2xl my-8" @click.away="$wire.closeModal()">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-[#3a2e24]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">{{ $isCreating ? 'add_circle' : 'edit' }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">{{ $isCreating ? 'Nouveau rôle' : 'Modifier le rôle' }}</h3>
                            <p class="text-sm text-text-secondary">{{ $isCreating ? 'Créez un nouveau rôle avec ses permissions' : 'Modifiez les permissions du rôle' }}</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6 max-h-[60vh] overflow-y-auto custom-scrollbar">
                    <!-- Role Name -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Nom du rôle</label>
                        <input
                            type="text"
                            wire:model="roleName"
                            class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-2.5 px-4 text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                            placeholder="ex: gestionnaire_stock"
                            {{ !$isCreating && in_array($editingRole?->name, ['super_admin', 'admin', 'employe']) ? 'disabled' : '' }}
                        >
                        @error('roleName')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Permissions by Group -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-3">Permissions</label>
                        <div class="space-y-4">
                            @foreach($permissionsByGroup as $group => $permissions)
                                <div class="border border-[#3a2e24] rounded-xl overflow-hidden">
                                    <div class="bg-background-dark px-4 py-3 flex items-center justify-between">
                                        <span class="text-white font-medium capitalize">{{ $group }}</span>
                                        <span class="text-xs text-text-secondary">{{ count($permissions) }} permissions</span>
                                    </div>
                                    <div class="p-4 grid grid-cols-2 gap-2">
                                        @foreach($permissions as $permission)
                                            <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-surface-highlight cursor-pointer transition-colors">
                                                <input
                                                    type="checkbox"
                                                    wire:model="selectedPermissions"
                                                    value="{{ $permission->name }}"
                                                    class="w-4 h-4 rounded border-[#3a2e24] bg-background-dark text-primary focus:ring-primary focus:ring-offset-0"
                                                    {{ !$isCreating && $editingRole?->name === 'super_admin' ? 'disabled' : '' }}
                                                >
                                                <span class="text-sm text-text-secondary">{{ $permission->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between gap-3 p-6 border-t border-[#3a2e24]">
                    <p class="text-sm text-text-secondary">
                        <span class="font-medium text-white">{{ count($selectedPermissions) }}</span> permissions sélectionnées
                    </p>
                    <div class="flex gap-3">
                        <button
                            wire:click="closeModal"
                            class="px-4 py-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-xl transition-colors"
                        >
                            Annuler
                        </button>
                        <button
                            wire:click="saveRole"
                            class="px-4 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl transition-colors flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            {{ $isCreating ? 'Créer' : 'Enregistrer' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
