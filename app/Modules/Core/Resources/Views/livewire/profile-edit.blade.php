<div class="space-y-6" x-data="{ activeTab: 'profile' }">
    <!-- Header -->
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">
                Mon Profil
            </h2>
            <p class="text-text-secondary text-sm">Gerez vos informations personnelles et votre mot de passe.</p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-2">
        <nav class="flex space-x-2">
            <button @click="activeTab = 'profile'"
                :class="activeTab === 'profile' ? 'bg-primary text-white' : 'text-text-secondary hover:text-white hover:bg-surface-highlight'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-colors">
                <span class="material-symbols-outlined text-[20px]">person</span>
                Informations personnelles
            </button>
            <button @click="activeTab = 'avatar'"
                :class="activeTab === 'avatar' ? 'bg-primary text-white' : 'text-text-secondary hover:text-white hover:bg-surface-highlight'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-colors">
                <span class="material-symbols-outlined text-[20px]">account_circle</span>
                Photo de profil
            </button>
            <button @click="activeTab = 'security'"
                :class="activeTab === 'security' ? 'bg-primary text-white' : 'text-text-secondary hover:text-white hover:bg-surface-highlight'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-colors">
                <span class="material-symbols-outlined text-[20px]">lock</span>
                Securite
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div>
        <!-- Tab: Informations personnelles -->
        <div x-show="activeTab === 'profile'" x-cloak>
            <x-ui.card>
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-4 bg-primary/20 rounded-xl">
                        <span class="material-symbols-outlined text-3xl text-primary">person</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Informations du profil</h3>
                        <p class="text-text-secondary text-sm">Mettez a jour vos informations personnelles.</p>
                    </div>
                </div>

                <form wire:submit="updateProfile" class="space-y-4 max-w-xl">
                    <x-ui.input wire:model="name" label="Nom complet *" placeholder="Votre nom complet" icon="person"
                        :error="$errors->first('name')" />

                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Adresse email</label>
                        <div class="flex items-center gap-2 px-4 py-3 bg-surface-highlight rounded-xl">
                            <span class="material-symbols-outlined text-text-secondary text-[20px]">mail</span>
                            <span class="text-white">{{ $user->email }}</span>
                            <span class="material-symbols-outlined text-green-400 text-[18px] ml-auto"
                                title="Verifie">verified</span>
                        </div>
                        <p class="text-xs text-text-secondary mt-1">L'adresse email ne peut pas etre modifiee.</p>
                    </div>

                    <x-ui.input wire:model="phone" label="Telephone" placeholder="+225 XX XX XX XX XX" icon="phone"
                        :error="$errors->first('phone')" />

                    <div class="bg-surface-highlight rounded-lg p-4 mt-4">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-text-secondary">badge</span>
                            <div>
                                <p class="text-white text-sm font-medium">Role:
                                    {{ $user->roles->pluck('name')->map(fn($r) => ucfirst(str_replace('_', ' ', $r)))->join(', ') ?: 'Utilisateur' }}
                                </p>
                                <p class="text-text-secondary text-xs">Membre depuis
                                    {{ $user->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <x-ui.button type="primary" :submit="true">
                            <span wire:loading.remove wire:target="updateProfile">Mettre a jour le profil</span>
                            <span wire:loading wire:target="updateProfile">Mise a jour...</span>
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <!-- Tab: Photo de profil -->
        <div x-show="activeTab === 'avatar'" x-cloak>
            <x-ui.card>
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-4 bg-indigo-500/20 rounded-xl">
                        <span class="material-symbols-outlined text-3xl text-indigo-400">account_circle</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Photo de profil</h3>
                        <p class="text-text-secondary text-sm">Personnalisez votre avatar.</p>
                    </div>
                </div>

                <div class="max-w-xl">
                    <div class="flex items-center gap-6 mb-6">
                        <!-- Avatar actuel -->
                        <div class="relative">
                            @if($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}"
                                    class="size-24 rounded-full object-cover ring-4 ring-primary/30" alt="Preview">
                            @elseif($current_avatar)
                                <img src="{{ Storage::url($current_avatar) }}"
                                    class="size-24 rounded-full object-cover ring-4 ring-primary/30"
                                    alt="{{ $user->name }}">
                            @else
                                <div
                                    class="size-24 rounded-full bg-primary/20 flex items-center justify-center ring-4 ring-primary/30">
                                    <span class="text-3xl font-bold text-primary">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                </div>
                            @endif
                            <div
                                class="absolute bottom-0 right-0 size-6 bg-green-500 rounded-full border-2 border-surface-dark">
                            </div>
                        </div>

                        <div>
                            <p class="text-white font-medium">{{ $user->name }}</p>
                            <p class="text-text-secondary text-sm">{{ $user->email }}</p>
                        </div>
                    </div>

                    <!-- Upload zone -->
                    <label
                        class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-[#3a2e24] rounded-xl cursor-pointer hover:bg-surface-highlight transition-colors">
                        <div class="flex flex-col items-center justify-center py-4">
                            <span
                                class="material-symbols-outlined text-3xl text-text-secondary mb-2">cloud_upload</span>
                            <p class="text-sm text-text-secondary">Cliquez pour telecharger une nouvelle photo</p>
                            <p class="text-xs text-text-secondary mt-1">PNG, JPG jusqu'a 2MB</p>
                        </div>
                        <input type="file" wire:model="avatar" class="hidden" accept="image/*">
                    </label>
                    @error('avatar') <p class="text-red-400 text-sm mt-2">{{ $message }}</p> @enderror

                    @if($current_avatar)
                        <div class="mt-4">
                            <button wire:click="removeAvatar"
                                wire:confirm="Etes-vous sur de vouloir supprimer votre photo de profil ?"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                Supprimer la photo
                            </button>
                        </div>
                    @endif

                    @if($avatar)
                        <div class="mt-4">
                            <x-ui.button type="primary" wire:click="updateProfile">
                                <span wire:loading.remove wire:target="updateProfile">Enregistrer la photo</span>
                                <span wire:loading wire:target="updateProfile">Enregistrement...</span>
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>

        <!-- Tab: Securite -->
        <div x-show="activeTab === 'security'" x-cloak>
            <x-ui.card>
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-4 bg-red-500/20 rounded-xl">
                        <span class="material-symbols-outlined text-3xl text-red-400">lock</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Changer le mot de passe</h3>
                        <p class="text-text-secondary text-sm">Assurez-vous d'utiliser un mot de passe fort et unique.
                        </p>
                    </div>
                </div>

                <form wire:submit="updatePassword" class="space-y-4 max-w-xl">
                    <x-ui.input type="password" wire:model="current_password" label="Mot de passe actuel *"
                        placeholder="Entrez votre mot de passe actuel" icon="lock"
                        :error="$errors->first('current_password')" />
                    <x-ui.input type="password" wire:model="new_password" label="Nouveau mot de passe *"
                        placeholder="Minimum 8 caracteres" icon="key" :error="$errors->first('new_password')" />
                    <x-ui.input type="password" wire:model="new_password_confirmation"
                        label="Confirmer le mot de passe *" placeholder="Repetez le nouveau mot de passe" icon="key" />

                    <div class="bg-surface-highlight rounded-lg p-4 mt-4">
                        <p class="text-text-secondary text-sm">
                            <span class="material-symbols-outlined text-[16px] mr-1 align-middle">info</span>
                            Le mot de passe doit contenir au moins 8 caracteres.
                        </p>
                    </div>

                    <div class="pt-4">
                        <x-ui.button type="primary" :submit="true">
                            <span wire:loading.remove wire:target="updatePassword">Changer le mot de passe</span>
                            <span wire:loading wire:target="updatePassword">Modification...</span>
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <!-- Session active -->
            <x-ui.card class="mt-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-4 bg-green-500/20 rounded-xl">
                        <span class="material-symbols-outlined text-3xl text-green-400">computer</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Session active</h3>
                        <p class="text-text-secondary text-sm">Votre connexion actuelle.</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-surface-highlight rounded-xl">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-2xl text-green-400">desktop_windows</span>
                        <div>
                            <p class="text-sm font-medium text-white">Session actuelle</p>
                            <p class="text-xs text-text-secondary">{{ request()->ip() }} - Connecte maintenant</p>
                        </div>
                    </div>
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-full bg-green-500/20 text-green-400 text-xs">
                        Active
                    </span>
                </div>
            </x-ui.card>
        </div>
    </div>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>