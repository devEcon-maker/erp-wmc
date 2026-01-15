<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestion des Utilisateurs</h1>
            <p class="text-text-secondary text-sm">Gerez les utilisateurs et leurs roles d'acces</p>
        </div>
        <div class="flex items-center gap-3">
            <button
                wire:click="openCreateModal"
                class="bg-primary hover:bg-primary/90 text-white font-bold py-2 px-4 rounded-xl flex items-center gap-2 transition-colors shadow-md"
            >
                <span class="material-symbols-outlined text-[20px]">person_add</span>
                Nouvel utilisateur
            </button>
            <a href="{{ route('admin.roles.index') }}" class="bg-surface-dark hover:bg-surface-highlight text-white font-bold py-2 px-4 rounded-xl flex items-center gap-2 transition-colors border border-[#3a2e24]">
                <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
                Gerer les Roles
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary">search</span>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher par nom ou email..."
                        class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-2.5 pl-10 pr-4 text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                    >
                </div>
            </div>
            <div class="sm:w-48">
                <select
                    wire:model.live="roleFilter"
                    class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-2.5 px-4 text-white focus:outline-none focus:border-primary"
                >
                    <option value="">Tous les rôles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-background-dark border-b border-[#3a2e24]">
                    <tr>
                        <th class="text-left px-6 py-4 text-xs font-bold text-text-secondary uppercase tracking-wider">Utilisateur</th>
                        <th class="text-left px-6 py-4 text-xs font-bold text-text-secondary uppercase tracking-wider">Rôles</th>
                        <th class="text-left px-6 py-4 text-xs font-bold text-text-secondary uppercase tracking-wider">Statut</th>
                        <th class="text-left px-6 py-4 text-xs font-bold text-text-secondary uppercase tracking-wider">Dernière connexion</th>
                        <th class="text-right px-6 py-4 text-xs font-bold text-text-secondary uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3a2e24]">
                    @forelse($users as $user)
                        <tr class="hover:bg-surface-highlight transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-cover bg-center border-2 border-[#3a2e24]"
                                        style="background-image: url('https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=f48c25&color=fff');">
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ $user->name }}</p>
                                        <p class="text-text-secondary text-sm">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        @php
                                            $colors = [
                                                'super_admin' => 'bg-red-500/20 text-red-400 border-red-500/30',
                                                'admin' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
                                                'commercial' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                                                'comptable' => 'bg-green-500/20 text-green-400 border-green-500/30',
                                                'rh' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                                                'manager' => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
                                                'employe' => 'bg-gray-500/20 text-gray-400 border-gray-500/30',
                                            ];
                                            $colorClass = $colors[$role->name] ?? 'bg-primary/20 text-primary border-primary/30';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium border {{ $colorClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                        </span>
                                    @empty
                                        <span class="text-text-secondary text-sm italic">Aucun rôle</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_active ?? true)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-green-500/20 text-green-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                                        Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-red-500/20 text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                        Inactif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-text-secondary text-sm">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        wire:click="openEditModal({{ $user->id }})"
                                        class="p-2 text-text-secondary hover:text-orange-400 hover:bg-orange-500/10 rounded-lg transition-colors"
                                        title="Modifier l'utilisateur"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <button
                                        wire:click="editUserRoles({{ $user->id }})"
                                        class="p-2 text-text-secondary hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                                        title="Modifier les rôles"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">shield_person</span>
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <button
                                            wire:click="toggleUserStatus({{ $user->id }})"
                                            wire:confirm="Êtes-vous sûr de vouloir {{ ($user->is_active ?? true) ? 'désactiver' : 'activer' }} cet utilisateur ?"
                                            class="p-2 text-text-secondary hover:text-{{ ($user->is_active ?? true) ? 'red' : 'green' }}-400 hover:bg-{{ ($user->is_active ?? true) ? 'red' : 'green' }}-500/10 rounded-lg transition-colors"
                                            title="{{ ($user->is_active ?? true) ? 'Désactiver' : 'Activer' }}"
                                        >
                                            <span class="material-symbols-outlined text-[20px]">{{ ($user->is_active ?? true) ? 'person_off' : 'person' }}</span>
                                        </button>
                                        <button
                                            wire:click="deleteUser({{ $user->id }})"
                                            wire:confirm="Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible."
                                            class="p-2 text-text-secondary hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
                                            title="Supprimer"
                                        >
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-4xl text-text-secondary mb-2 block">person_search</span>
                                <p class="text-text-secondary">Aucun utilisateur trouvé</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-[#3a2e24]">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Edit Roles -->
    @if($showModal && $editingUser)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl w-full max-w-md shadow-2xl" @click.away="$wire.closeModal()">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-[#3a2e24]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">shield_person</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Modifier les rôles</h3>
                            <p class="text-sm text-text-secondary">{{ $editingUser->name }}</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <p class="text-sm text-text-secondary mb-4">Sélectionnez les rôles à attribuer à cet utilisateur :</p>

                    <div class="space-y-2">
                        @foreach($roles as $role)
                            @php
                                $colors = [
                                    'super_admin' => 'border-red-500/50 bg-red-500/10',
                                    'admin' => 'border-purple-500/50 bg-purple-500/10',
                                    'commercial' => 'border-blue-500/50 bg-blue-500/10',
                                    'comptable' => 'border-green-500/50 bg-green-500/10',
                                    'rh' => 'border-yellow-500/50 bg-yellow-500/10',
                                    'manager' => 'border-orange-500/50 bg-orange-500/10',
                                    'employe' => 'border-gray-500/50 bg-gray-500/10',
                                ];
                                $colorClass = $colors[$role->name] ?? 'border-primary/50 bg-primary/10';
                            @endphp
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-[#3a2e24] hover:border-primary/30 cursor-pointer transition-colors {{ in_array($role->name, $selectedRoles) ? $colorClass : '' }}">
                                <input
                                    type="checkbox"
                                    wire:model="selectedRoles"
                                    value="{{ $role->name }}"
                                    class="w-5 h-5 rounded border-[#3a2e24] bg-background-dark text-primary focus:ring-primary focus:ring-offset-0"
                                >
                                <div class="flex-1">
                                    <p class="text-white font-medium">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</p>
                                    <p class="text-xs text-text-secondary">{{ $role->permissions->count() }} permissions</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 p-6 border-t border-[#3a2e24]">
                    <button
                        wire:click="closeModal"
                        class="px-4 py-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-xl transition-colors"
                    >
                        Annuler
                    </button>
                    <button
                        wire:click="updateUserRoles"
                        class="px-4 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl transition-colors flex items-center gap-2"
                    >
                        <span class="material-symbols-outlined text-[20px]">save</span>
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Create User -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl w-full max-w-lg shadow-2xl" @click.away="$wire.closeCreateModal()">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-[#3a2e24]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">person_add</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Nouvel utilisateur</h3>
                            <p class="text-sm text-text-secondary">Creer un nouveau compte utilisateur</p>
                        </div>
                    </div>
                    <button wire:click="closeCreateModal" class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <form wire:submit="createUser">
                    <div class="p-6 space-y-4">
                        <!-- Nom -->
                        <div>
                            <label class="block text-sm font-medium text-white mb-2">Nom complet *</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary">person</span>
                                <input
                                    type="text"
                                    wire:model="newUserName"
                                    placeholder="Jean Dupont"
                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-3 pl-10 pr-4 text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                                >
                            </div>
                            @error('newUserName')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-white mb-2">Adresse email *</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary">mail</span>
                                <input
                                    type="email"
                                    wire:model="newUserEmail"
                                    placeholder="jean.dupont@entreprise.com"
                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-3 pl-10 pr-4 text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                                >
                            </div>
                            @error('newUserEmail')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Mot de passe -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Mot de passe *</label>
                                <div class="relative" x-data="{ show: false }">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary">lock</span>
                                    <input
                                        :type="show ? 'text' : 'password'"
                                        wire:model="newUserPassword"
                                        placeholder="••••••••"
                                        class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-3 pl-10 pr-10 text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                                    >
                                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary hover:text-primary">
                                        <span x-show="!show" class="material-symbols-outlined text-[20px]">visibility_off</span>
                                        <span x-show="show" class="material-symbols-outlined text-[20px]">visibility</span>
                                    </button>
                                </div>
                                @error('newUserPassword')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Confirmer *</label>
                                <div class="relative" x-data="{ show: false }">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary">lock</span>
                                    <input
                                        :type="show ? 'text' : 'password'"
                                        wire:model="newUserPasswordConfirmation"
                                        placeholder="••••••••"
                                        class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-3 pl-10 pr-10 text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                                    >
                                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary hover:text-primary">
                                        <span x-show="!show" class="material-symbols-outlined text-[20px]">visibility_off</span>
                                        <span x-show="show" class="material-symbols-outlined text-[20px]">visibility</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Roles -->
                        <div>
                            <label class="block text-sm font-medium text-white mb-2">Roles (optionnel)</label>
                            <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto p-1">
                                @foreach($roles as $role)
                                    @php
                                        $colors = [
                                            'super_admin' => 'border-red-500/50 bg-red-500/10',
                                            'admin' => 'border-purple-500/50 bg-purple-500/10',
                                            'commercial' => 'border-blue-500/50 bg-blue-500/10',
                                            'comptable' => 'border-green-500/50 bg-green-500/10',
                                            'rh' => 'border-yellow-500/50 bg-yellow-500/10',
                                            'manager' => 'border-orange-500/50 bg-orange-500/10',
                                            'employe' => 'border-gray-500/50 bg-gray-500/10',
                                        ];
                                        $colorClass = $colors[$role->name] ?? 'border-primary/50 bg-primary/10';
                                    @endphp
                                    <label class="flex items-center gap-2 p-2 rounded-lg border border-[#3a2e24] hover:border-primary/30 cursor-pointer transition-colors {{ in_array($role->name, $newUserRoles) ? $colorClass : '' }}">
                                        <input
                                            type="checkbox"
                                            wire:model="newUserRoles"
                                            value="{{ $role->name }}"
                                            class="w-4 h-4 rounded border-[#3a2e24] bg-background-dark text-primary focus:ring-primary focus:ring-offset-0"
                                        >
                                        <span class="text-white text-sm">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-blue-400 text-[18px] mt-0.5">info</span>
                                <p class="text-blue-400 text-xs">
                                    L'utilisateur recevra ses identifiants de connexion. Assurez-vous de lui communiquer son mot de passe de maniere securisee.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 p-6 border-t border-[#3a2e24]">
                        <button
                            type="button"
                            wire:click="closeCreateModal"
                            class="px-4 py-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-xl transition-colors"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl transition-colors flex items-center gap-2"
                            wire:loading.attr="disabled"
                            wire:target="createUser"
                        >
                            <span wire:loading.remove wire:target="createUser" class="material-symbols-outlined text-[20px]">person_add</span>
                            <span wire:loading wire:target="createUser" class="material-symbols-outlined animate-spin text-[20px]">refresh</span>
                            <span wire:loading.remove wire:target="createUser">Creer l'utilisateur</span>
                            <span wire:loading wire:target="createUser">Creation...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Edit User -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl w-full max-w-lg shadow-2xl" @click.away="$wire.closeEditModal()">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-[#3a2e24]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-orange-500/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-orange-400">edit</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Modifier l'utilisateur</h3>
                            <p class="text-sm text-text-secondary">Modifier les informations du compte</p>
                        </div>
                    </div>
                    <button wire:click="closeEditModal" class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <form wire:submit="updateUser">
                    <div class="p-6 space-y-4">
                        <!-- Nom -->
                        <div>
                            <label class="block text-sm font-medium text-white mb-2">Nom complet *</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary">person</span>
                                <input
                                    type="text"
                                    wire:model="editUserName"
                                    placeholder="Jean Dupont"
                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-3 pl-10 pr-4 text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                                >
                            </div>
                            @error('editUserName')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Telephone -->
                        <div>
                            <label class="block text-sm font-medium text-white mb-2">Telephone</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary">phone</span>
                                <input
                                    type="text"
                                    wire:model="editUserPhone"
                                    placeholder="+225 XX XX XX XX XX"
                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-3 pl-10 pr-4 text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                                >
                            </div>
                            @error('editUserPhone')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Mot de passe -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Nouveau mot de passe *</label>
                                <div class="relative" x-data="{ show: false }">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary">lock</span>
                                    <input
                                        :type="show ? 'text' : 'password'"
                                        wire:model="editUserPassword"
                                        placeholder="••••••••"
                                        class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-3 pl-10 pr-10 text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                                    >
                                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary hover:text-primary">
                                        <span x-show="!show" class="material-symbols-outlined text-[20px]">visibility_off</span>
                                        <span x-show="show" class="material-symbols-outlined text-[20px]">visibility</span>
                                    </button>
                                </div>
                                @error('editUserPassword')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Confirmer *</label>
                                <div class="relative" x-data="{ show: false }">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary">lock</span>
                                    <input
                                        :type="show ? 'text' : 'password'"
                                        wire:model="editUserPasswordConfirmation"
                                        placeholder="••••••••"
                                        class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-3 pl-10 pr-10 text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                                    >
                                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary hover:text-primary">
                                        <span x-show="!show" class="material-symbols-outlined text-[20px]">visibility_off</span>
                                        <span x-show="show" class="material-symbols-outlined text-[20px]">visibility</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 p-6 border-t border-[#3a2e24]">
                        <button
                            type="button"
                            wire:click="closeEditModal"
                            class="px-4 py-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-xl transition-colors"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl transition-colors flex items-center gap-2"
                            wire:loading.attr="disabled"
                            wire:target="updateUser"
                        >
                            <span wire:loading.remove wire:target="updateUser" class="material-symbols-outlined text-[20px]">save</span>
                            <span wire:loading wire:target="updateUser" class="material-symbols-outlined animate-spin text-[20px]">refresh</span>
                            <span wire:loading.remove wire:target="updateUser">Enregistrer</span>
                            <span wire:loading wire:target="updateUser">Enregistrement...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
