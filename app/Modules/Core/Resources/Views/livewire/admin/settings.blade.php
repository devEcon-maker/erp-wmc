<div class="space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">
                Parametres
            </h2>
            <p class="text-text-secondary text-sm">Configuration de l'application et du compte utilisateur.</p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-2">
        <nav class="flex space-x-2">
            <button wire:click="$set('activeTab', 'smtp')"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $activeTab === 'smtp' ? 'bg-primary text-white' : 'text-text-secondary hover:text-white hover:bg-surface-highlight' }}">
                <span class="material-symbols-outlined text-[20px]">mail</span>
                Configuration Email (SMTP)
            </button>
            <button wire:click="$set('activeTab', 'profile')"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $activeTab === 'profile' ? 'bg-primary text-white' : 'text-text-secondary hover:text-white hover:bg-surface-highlight' }}">
                <span class="material-symbols-outlined text-[20px]">person</span>
                Mon Profil
            </button>
            <button wire:click="$set('activeTab', 'security')"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $activeTab === 'security' ? 'bg-primary text-white' : 'text-text-secondary hover:text-white hover:bg-surface-highlight' }}">
                <span class="material-symbols-outlined text-[20px]">lock</span>
                Securite
            </button>
            @if(auth()->user()->hasRole('super_admin'))
                <button wire:click="$set('activeTab', 'updates')"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $activeTab === 'updates' ? 'bg-primary text-white' : 'text-text-secondary hover:text-white hover:bg-surface-highlight' }}">
                    <span class="material-symbols-outlined text-[20px]">system_update</span>
                    Mises a jour
                </button>
            @endif
        </nav>
    </div>

    <!-- Tab Content: SMTP -->
    @if($activeTab === 'smtp')
        <div class="space-y-6">
            <!-- Header SMTP -->
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-white">Configurations SMTP</h3>
                    <p class="text-text-secondary text-sm">Gerez vos serveurs d'envoi d'emails. La configuration par defaut sera utilisee pour tous les envois.</p>
                </div>
                <x-ui.button wire:click="createSmtp" type="primary" icon="add">
                    Ajouter SMTP
                </x-ui.button>
            </div>

            <!-- Liste des configurations SMTP -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @forelse($smtpConfigurations as $smtp)
                    <div class="bg-surface-dark border {{ $smtp->is_default ? 'border-primary' : 'border-[#3a2e24]' }} rounded-xl p-5 relative">
                        @if($smtp->is_default)
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-primary/20 text-primary">
                                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                    Par defaut
                                </span>
                            </div>
                        @endif

                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 p-3 bg-surface-highlight rounded-xl">
                                <span class="material-symbols-outlined text-2xl {{ $smtp->is_active ? 'text-green-400' : 'text-text-secondary' }}">
                                    {{ $smtp->is_active ? 'cloud_done' : 'cloud_off' }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-white font-bold truncate">{{ $smtp->name }}</h4>
                                <p class="text-text-secondary text-sm">{{ $smtp->host }}:{{ $smtp->port }}</p>
                                <p class="text-text-secondary text-xs mt-1">{{ $smtp->from_address }}</p>

                                <!-- Status de test -->
                                @if($smtp->last_tested_at)
                                    <div class="mt-2 flex items-center gap-2">
                                        @if($smtp->last_test_successful)
                                            <span class="inline-flex items-center gap-1 text-xs text-green-400">
                                                <span class="material-symbols-outlined text-[14px]">check</span>
                                                Test OK - {{ $smtp->last_tested_at->diffForHumans() }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs text-red-400">
                                                <span class="material-symbols-outlined text-[14px]">error</span>
                                                Echec - {{ $smtp->last_tested_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-4 pt-4 border-t border-[#3a2e24] flex flex-wrap gap-2">
                            <button wire:click="editSmtp({{ $smtp->id }})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg bg-surface-highlight text-text-secondary hover:text-white transition-colors">
                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                Modifier
                            </button>
                            <button wire:click="testSmtpConnection({{ $smtp->id }})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg bg-surface-highlight text-text-secondary hover:text-cyan-400 transition-colors">
                                <span class="material-symbols-outlined text-[16px]">wifi_tethering</span>
                                Tester
                            </button>
                            <button wire:click="openTestModal({{ $smtp->id }})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg bg-surface-highlight text-text-secondary hover:text-indigo-400 transition-colors">
                                <span class="material-symbols-outlined text-[16px]">send</span>
                                Email Test
                            </button>
                            @if(!$smtp->is_default)
                                <button wire:click="setDefaultSmtp({{ $smtp->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg bg-surface-highlight text-text-secondary hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">star</span>
                                    Definir par defaut
                                </button>
                            @endif
                            <button wire:click="toggleSmtpStatus({{ $smtp->id }})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg bg-surface-highlight text-text-secondary hover:text-yellow-400 transition-colors">
                                <span class="material-symbols-outlined text-[16px]">{{ $smtp->is_active ? 'toggle_on' : 'toggle_off' }}</span>
                                {{ $smtp->is_active ? 'Desactiver' : 'Activer' }}
                            </button>
                            @if(!$smtp->is_default)
                                <button wire:click="deleteSmtp({{ $smtp->id }})"
                                    wire:confirm="Etes-vous sur de vouloir supprimer cette configuration ?"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg bg-surface-highlight text-text-secondary hover:text-red-400 transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                    Supprimer
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 bg-surface-dark border border-[#3a2e24] rounded-xl p-12 text-center">
                        <span class="material-symbols-outlined text-4xl text-text-secondary mb-3">mail</span>
                        <p class="text-text-secondary">Aucune configuration SMTP.</p>
                        <button wire:click="createSmtp" class="mt-4 text-primary hover:underline">
                            Ajouter votre premiere configuration
                        </button>
                    </div>
                @endforelse
            </div>

            <!-- Info box -->
            <x-ui.card>
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-2xl text-blue-400">info</span>
                    <div>
                        <h4 class="text-white font-medium">A propos des configurations SMTP</h4>
                        <p class="text-text-secondary text-sm mt-1">
                            La configuration marquee "Par defaut" sera utilisee pour envoyer tous les emails de l'application
                            (notifications, relances, factures, etc.). Vous pouvez ajouter plusieurs serveurs SMTP
                            et basculer entre eux selon vos besoins.
                        </p>
                    </div>
                </div>
            </x-ui.card>
        </div>
    @endif

    <!-- Tab Content: Profile -->
    @if($activeTab === 'profile')
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
                <x-ui.input wire:model="user_name" label="Nom complet *" :error="$errors->first('user_name')" />
                <x-ui.input type="email" wire:model="user_email" label="Adresse email *" :error="$errors->first('user_email')" />

                <div class="pt-4">
                    <x-ui.button type="primary" submit>
                        Mettre a jour le profil
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @endif

    <!-- Tab Content: Security -->
    @if($activeTab === 'security')
        <x-ui.card>
            <div class="flex items-center gap-4 mb-6">
                <div class="p-4 bg-red-500/20 rounded-xl">
                    <span class="material-symbols-outlined text-3xl text-red-400">lock</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Changer le mot de passe</h3>
                    <p class="text-text-secondary text-sm">Assurez-vous d'utiliser un mot de passe fort et unique.</p>
                </div>
            </div>

            <form wire:submit="updatePassword" class="space-y-4 max-w-xl">
                <x-ui.input type="password" wire:model="current_password" label="Mot de passe actuel *" :error="$errors->first('current_password')" />
                <x-ui.input type="password" wire:model="new_password" label="Nouveau mot de passe *" :error="$errors->first('new_password')" />
                <x-ui.input type="password" wire:model="new_password_confirmation" label="Confirmer le mot de passe *" />

                <div class="bg-surface-highlight rounded-lg p-4 mt-4">
                    <p class="text-text-secondary text-sm">
                        <span class="material-symbols-outlined text-[16px] mr-1 align-middle">info</span>
                        Le mot de passe doit contenir au moins 8 caracteres.
                    </p>
                </div>

                <div class="pt-4">
                    <x-ui.button type="primary" submit>
                        Changer le mot de passe
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @endif

    <!-- Tab Content: Updates (Super Admin only) -->
    @if($activeTab === 'updates' && auth()->user()->hasRole('super_admin'))
        <div class="space-y-6">
            <!-- Version actuelle -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <x-ui.card class="lg:col-span-2">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-4 bg-primary/20 rounded-xl">
                                <span class="material-symbols-outlined text-3xl text-primary">verified</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">ERP WMC</h3>
                                <p class="text-2xl font-bold text-primary">v{{ $versionInfo['version'] ?? '1.0.0' }}</p>
                                <p class="text-text-secondary text-sm mt-1">
                                    Build {{ $versionInfo['build'] ?? '0' }} - {{ $versionInfo['codename'] ?? 'Genesis' }}
                                </p>
                                <p class="text-text-secondary text-xs mt-1">
                                    Date de sortie: {{ $versionInfo['release_date'] ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <button wire:click="checkForUpdates"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-surface-highlight text-white hover:bg-surface-highlight/80 transition-colors"
                                wire:loading.attr="disabled"
                                wire:target="checkForUpdates">
                                <div wire:loading.remove wire:target="checkForUpdates" class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">refresh</span>
                                    <span>Verifier les mises a jour</span>
                                </div>
                                <div wire:loading wire:target="checkForUpdates" class="flex items-center gap-2">
                                    <span class="material-symbols-outlined animate-spin text-[18px]">refresh</span>
                                    <span>Verification...</span>
                                </div>
                            </button>
                            <button wire:click="openUploadModal"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-primary text-white hover:bg-primary/80 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">upload_file</span>
                                Upload manuel
                            </button>
                        </div>
                    </div>

                    @if($updateInfo)
                        <div class="mt-6 pt-6 border-t border-[#3a2e24]">
                            @if($updateInfo['update_available'] ?? false)
                                <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-2xl text-green-400">new_releases</span>
                                        <div>
                                            <p class="text-white font-bold">Nouvelle version disponible: v{{ $updateInfo['latest_version'] }}</p>
                                            <p class="text-text-secondary text-sm">Une mise a jour est disponible pour votre ERP.</p>
                                        </div>
                                    </div>

                                    @if(!empty($updateInfo['latest_info']['changelog']))
                                        <div class="mt-4 bg-surface-highlight rounded-lg p-4">
                                            <p class="text-sm text-white font-medium mb-2">Changelog:</p>
                                            <div class="text-text-secondary text-sm prose prose-sm prose-invert max-w-none">
                                                {!! nl2br(e($updateInfo['latest_info']['changelog'])) !!}
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-4 flex gap-3">
                                        <button wire:click="createBackup"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-surface-highlight text-white hover:bg-surface-highlight/80 transition-colors"
                                            wire:loading.attr="disabled">
                                            <span class="material-symbols-outlined text-[18px]">backup</span>
                                            Creer une sauvegarde d'abord
                                        </button>
                                        <button wire:click="applyUpdate"
                                            wire:confirm="Etes-vous sur de vouloir appliquer cette mise a jour ? Une sauvegarde sera creee automatiquement."
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-green-600 text-white hover:bg-green-700 transition-colors"
                                            wire:loading.attr="disabled"
                                            wire:target="applyUpdate">
                                            <div wire:loading.remove wire:target="applyUpdate" class="flex items-center gap-2">
                                                <span class="material-symbols-outlined text-[18px]">system_update_alt</span>
                                                <span>Appliquer la mise a jour</span>
                                            </div>
                                            <div wire:loading wire:target="applyUpdate" class="flex items-center gap-2">
                                                <span class="material-symbols-outlined animate-spin text-[18px]">refresh</span>
                                                <span>Mise a jour en cours...</span>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="bg-surface-highlight rounded-xl p-4">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-2xl text-green-400">check_circle</span>
                                        <div>
                                            <p class="text-white font-medium">Votre ERP est a jour</p>
                                            <p class="text-text-secondary text-sm">
                                                Version actuelle: v{{ $updateInfo['current_version'] }}
                                                @if(isset($updateInfo['error']))
                                                    <span class="text-yellow-400 block mt-1">{{ $updateInfo['error'] }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </x-ui.card>

                <!-- Prerequis systeme -->
                <x-ui.card>
                    <h4 class="text-white font-bold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">checklist</span>
                        Prerequis systeme
                    </h4>
                    <div class="space-y-3">
                        <!-- PHP Version -->
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary text-sm">PHP</span>
                            <span class="flex items-center gap-2">
                                <span class="text-white text-sm">{{ $systemRequirements['php']['current'] ?? PHP_VERSION }}</span>
                                @if($systemRequirements['php']['passed'] ?? true)
                                    <span class="material-symbols-outlined text-[16px] text-green-400">check_circle</span>
                                @else
                                    <span class="material-symbols-outlined text-[16px] text-red-400">error</span>
                                @endif
                            </span>
                        </div>

                        <!-- Extensions -->
                        @foreach(($systemRequirements['extensions'] ?? []) as $ext => $info)
                            <div class="flex items-center justify-between">
                                <span class="text-text-secondary text-sm">{{ ucfirst($ext) }}</span>
                                @if($info['installed'] ?? false)
                                    <span class="material-symbols-outlined text-[16px] text-green-400">check_circle</span>
                                @else
                                    <span class="material-symbols-outlined text-[16px] text-red-400">error</span>
                                @endif
                            </div>
                        @endforeach

                        <!-- Permissions -->
                        <div class="pt-2 border-t border-[#3a2e24]">
                            <p class="text-text-secondary text-xs mb-2">Permissions</p>
                            <div class="flex items-center justify-between">
                                <span class="text-text-secondary text-sm">storage/</span>
                                @if($systemRequirements['permissions']['storage'] ?? false)
                                    <span class="material-symbols-outlined text-[16px] text-green-400">check_circle</span>
                                @else
                                    <span class="material-symbols-outlined text-[16px] text-red-400">error</span>
                                @endif
                            </div>
                        </div>

                        <!-- Disk Space -->
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary text-sm">Espace disque</span>
                            @if($systemRequirements['disk_space']['passed'] ?? true)
                                <span class="material-symbols-outlined text-[16px] text-green-400">check_circle</span>
                            @else
                                <span class="material-symbols-outlined text-[16px] text-red-400">error</span>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <!-- Sauvegardes -->
            <x-ui.card>
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-500/20 rounded-xl">
                            <span class="material-symbols-outlined text-2xl text-blue-400">backup</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Sauvegardes</h3>
                            <p class="text-text-secondary text-sm">Gerez les sauvegardes de votre application.</p>
                        </div>
                    </div>
                    <button wire:click="createBackup"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-blue-600 text-white hover:bg-blue-700 transition-colors"
                        wire:loading.attr="disabled"
                        wire:target="createBackup">
                        <div wire:loading.remove wire:target="createBackup" class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            <span>Nouvelle sauvegarde</span>
                        </div>
                        <div wire:loading wire:target="createBackup" class="flex items-center gap-2">
                            <span class="material-symbols-outlined animate-spin text-[18px]">refresh</span>
                            <span>Creation...</span>
                        </div>
                    </button>
                </div>

                @if(count($backups ?? []) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-[#3a2e24]">
                                    <th class="text-left py-3 px-4 text-text-secondary text-sm font-medium">Date</th>
                                    <th class="text-left py-3 px-4 text-text-secondary text-sm font-medium">Version</th>
                                    <th class="text-left py-3 px-4 text-text-secondary text-sm font-medium">Taille</th>
                                    <th class="text-right py-3 px-4 text-text-secondary text-sm font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($backups as $backup)
                                    <tr class="border-b border-[#3a2e24]/50 hover:bg-surface-highlight/30">
                                        <td class="py-3 px-4">
                                            <span class="text-white text-sm">{{ $backup['name'] ?? '' }}</span>
                                            <span class="text-text-secondary text-xs block">
                                                {{ isset($backup['created_at']) ? \Carbon\Carbon::parse($backup['created_at'])->format('d/m/Y H:i') : '' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-text-secondary text-sm">v{{ $backup['version'] ?? 'N/A' }}</td>
                                        <td class="py-3 px-4 text-text-secondary text-sm">{{ $backup['size'] ?? 'N/A' }}</td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button wire:click="restoreBackup('{{ $backup['name'] ?? '' }}')"
                                                    wire:confirm="Etes-vous sur de vouloir restaurer cette sauvegarde ? Toutes les donnees actuelles seront remplacees."
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg bg-surface-highlight text-text-secondary hover:text-yellow-400 transition-colors">
                                                    <span class="material-symbols-outlined text-[16px]">restore</span>
                                                    Restaurer
                                                </button>
                                                <button wire:click="deleteBackup('{{ $backup['name'] ?? '' }}')"
                                                    wire:confirm="Etes-vous sur de vouloir supprimer cette sauvegarde ?"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg bg-surface-highlight text-text-secondary hover:text-red-400 transition-colors">
                                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                                    Supprimer
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <span class="material-symbols-outlined text-4xl text-text-secondary mb-3">cloud_off</span>
                        <p class="text-text-secondary">Aucune sauvegarde disponible.</p>
                        <p class="text-text-secondary text-sm">Creez votre premiere sauvegarde avant de mettre a jour.</p>
                    </div>
                @endif
            </x-ui.card>

            <!-- Historique des mises a jour -->
            <x-ui.card>
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-purple-500/20 rounded-xl">
                        <span class="material-symbols-outlined text-2xl text-purple-400">history</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Historique des mises a jour</h3>
                        <p class="text-text-secondary text-sm">Liste des mises a jour appliquees.</p>
                    </div>
                </div>

                @if(count($updateHistory ?? []) > 0)
                    <div class="space-y-3">
                        @foreach($updateHistory as $update)
                            <div class="flex items-center justify-between bg-surface-highlight rounded-lg p-3">
                                <div class="flex items-center gap-3">
                                    @if($update->success)
                                        <span class="material-symbols-outlined text-green-400">check_circle</span>
                                    @else
                                        <span class="material-symbols-outlined text-red-400">error</span>
                                    @endif
                                    <div>
                                        <span class="text-white font-medium">v{{ $update->version }}</span>
                                        @if($update->build)
                                            <span class="text-text-secondary text-xs ml-2">Build {{ $update->build }}</span>
                                        @endif
                                        @if($update->notes)
                                            <p class="text-text-secondary text-xs mt-1">{{ $update->notes }}</p>
                                        @endif
                                        @if(!$update->success && $update->error_message)
                                            <p class="text-red-400 text-xs mt-1">{{ $update->error_message }}</p>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-text-secondary text-sm">
                                    {{ \Carbon\Carbon::parse($update->applied_at)->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <span class="material-symbols-outlined text-3xl text-text-secondary mb-2">update</span>
                        <p class="text-text-secondary text-sm">Aucun historique de mise a jour.</p>
                    </div>
                @endif
            </x-ui.card>

            <!-- Info box -->
            <x-ui.card>
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-2xl text-yellow-400">warning</span>
                    <div>
                        <h4 class="text-white font-medium">Important</h4>
                        <p class="text-text-secondary text-sm mt-1">
                            Avant d'effectuer une mise a jour, il est fortement recommande de creer une sauvegarde complete
                            de votre application et de votre base de donnees. En cas de probleme, vous pourrez restaurer
                            la version precedente.
                        </p>
                    </div>
                </div>
            </x-ui.card>
        </div>
    @endif

    <!-- Modal SMTP -->
    @if($showSmtpModal)
        <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/70 transition-opacity" wire:click="closeSmtpModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-surface-dark rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-[#3a2e24]">
                    <form wire:submit="saveSmtp">
                        <div class="bg-surface-dark px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-bold text-white">
                                    {{ $editingSmtp ? 'Modifier la configuration SMTP' : 'Nouvelle configuration SMTP' }}
                                </h3>
                                <button type="button" wire:click="closeSmtpModal" class="text-text-secondary hover:text-white">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <!-- Nom -->
                                <x-ui.input wire:model="smtp_name" label="Nom de la configuration *"
                                    placeholder="Ex: Gmail, OVH, Serveur Principal" :error="$errors->first('smtp_name')" />

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Host -->
                                    <x-ui.input wire:model="smtp_host" label="Hote SMTP *"
                                        placeholder="smtp.gmail.com" :error="$errors->first('smtp_host')" />

                                    <!-- Port -->
                                    <x-ui.select wire:model="smtp_port" label="Port *" :error="$errors->first('smtp_port')">
                                        @foreach($commonPorts as $port => $label)
                                            <option value="{{ $port }}">{{ $label }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Encryption -->
                                    <x-ui.select wire:model="smtp_encryption" label="Chiffrement *" :error="$errors->first('smtp_encryption')">
                                        @foreach($encryptions as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </x-ui.select>

                                    <div></div>
                                </div>

                                <div class="border-t border-[#3a2e24] pt-4 mt-4">
                                    <p class="text-sm text-text-secondary mb-4">Authentification</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Username -->
                                    <x-ui.input wire:model="smtp_username" label="Nom d'utilisateur *"
                                        placeholder="votre@email.com" :error="$errors->first('smtp_username')" />

                                    <!-- Password -->
                                    <x-ui.input type="password" wire:model="smtp_password"
                                        label="{{ $editingSmtp ? 'Mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe *' }}"
                                        :error="$errors->first('smtp_password')" />
                                </div>

                                <div class="border-t border-[#3a2e24] pt-4 mt-4">
                                    <p class="text-sm text-text-secondary mb-4">Expediteur par defaut</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- From Address -->
                                    <x-ui.input type="email" wire:model="smtp_from_address" label="Adresse d'expediteur *"
                                        placeholder="noreply@votreentreprise.com" :error="$errors->first('smtp_from_address')" />

                                    <!-- From Name -->
                                    <x-ui.input wire:model="smtp_from_name" label="Nom d'expediteur *"
                                        placeholder="ERP WMC" :error="$errors->first('smtp_from_name')" />
                                </div>
                            </div>
                        </div>

                        <div class="bg-surface-highlight px-6 py-4 flex justify-end gap-3">
                            <x-ui.button type="secondary" wire:click="closeSmtpModal">
                                Annuler
                            </x-ui.button>
                            <x-ui.button type="primary" submit>
                                <div wire:loading.remove wire:target="saveSmtp">
                                    {{ $editingSmtp ? 'Mettre a jour' : 'Creer la configuration' }}
                                </div>
                                <div wire:loading wire:target="saveSmtp" class="flex items-center gap-2">
                                    <span class="material-symbols-outlined animate-spin text-[18px]">refresh</span>
                                    Enregistrement...
                                </div>
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Test Email -->
    @if($showTestModal && $testingSmtp)
        <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/70 transition-opacity" wire:click="$set('showTestModal', false)"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-surface-dark rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-[#3a2e24]">
                    <div class="bg-surface-dark px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-white">Envoyer un email de test</h3>
                            <button type="button" wire:click="$set('showTestModal', false)" class="text-text-secondary hover:text-white">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-surface-highlight rounded-lg p-4">
                                <p class="text-sm text-white font-medium">{{ $testingSmtp->name }}</p>
                                <p class="text-xs text-text-secondary">{{ $testingSmtp->host }}:{{ $testingSmtp->port }}</p>
                            </div>

                            <x-ui.input type="email" wire:model="test_email" label="Adresse email de destination *"
                                placeholder="destinataire@email.com" :error="$errors->first('test_email')" />
                        </div>
                    </div>

                    <div class="bg-surface-highlight px-6 py-4 flex justify-end gap-3">
                        <x-ui.button type="secondary" wire:click="$set('showTestModal', false)">
                            Annuler
                        </x-ui.button>
                        <x-ui.button type="primary" wire:click="sendTestEmail">
                            <div wire:loading.remove wire:target="sendTestEmail">
                                Envoyer le test
                            </div>
                            <div wire:loading wire:target="sendTestEmail" class="flex items-center gap-2">
                                <span class="material-symbols-outlined animate-spin text-[18px]">refresh</span>
                                Envoi...
                            </div>
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Upload Mise à jour -->
    @if($showUploadModal)
        <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/70 transition-opacity" wire:click="closeUploadModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-surface-dark rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-[#3a2e24]">
                    <form wire:submit="applyManualUpdate">
                        <div class="bg-surface-dark px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-bold text-white">Upload de mise a jour</h3>
                                <button type="button" wire:click="closeUploadModal" class="text-text-secondary hover:text-white">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div class="bg-surface-highlight rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <span class="material-symbols-outlined text-blue-400">info</span>
                                        <div class="text-sm text-text-secondary">
                                            <p class="font-medium text-white mb-1">Instructions:</p>
                                            <ul class="list-disc list-inside space-y-1">
                                                <li>Selectionnez le fichier ZIP de mise a jour</li>
                                                <li>Une sauvegarde sera creee automatiquement</li>
                                                <li>Le fichier doit contenir le code source mis a jour</li>
                                                <li>Taille max: 100 Mo</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-white mb-2">Fichier de mise a jour (.zip) *</label>
                                    <div class="relative">
                                        <input type="file"
                                            wire:model="updateFile"
                                            accept=".zip"
                                            class="block w-full text-sm text-text-secondary
                                                file:mr-4 file:py-2 file:px-4
                                                file:rounded-lg file:border-0
                                                file:text-sm file:font-medium
                                                file:bg-primary file:text-white
                                                hover:file:bg-primary/80
                                                file:cursor-pointer cursor-pointer
                                                bg-surface-highlight rounded-lg p-2" />
                                    </div>
                                    @error('updateFile')
                                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                    @enderror

                                    <div wire:loading wire:target="updateFile" class="mt-2 text-sm text-blue-400 flex items-center gap-2">
                                        <span class="material-symbols-outlined animate-spin text-[16px]">refresh</span>
                                        Telechargement du fichier...
                                    </div>

                                    @if($updateFile)
                                        <div class="mt-2 bg-green-500/10 border border-green-500/30 rounded-lg p-3">
                                            <div class="flex items-center gap-2 text-green-400 text-sm">
                                                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                                <span>{{ $updateFile->getClientOriginalName() }}</span>
                                                <span class="text-text-secondary">({{ number_format($updateFile->getSize() / 1024 / 1024, 2) }} Mo)</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-3">
                                    <div class="flex items-start gap-2">
                                        <span class="material-symbols-outlined text-yellow-400 text-[18px]">warning</span>
                                        <p class="text-yellow-400 text-sm">
                                            Attention: L'application sera en maintenance pendant la mise a jour.
                                            Assurez-vous qu'aucun utilisateur n'est connecte.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-surface-highlight px-6 py-4 flex justify-end gap-3">
                            <x-ui.button type="secondary" wire:click="closeUploadModal">
                                Annuler
                            </x-ui.button>
                            <x-ui.button type="primary" submit :disabled="!$updateFile">
                                <div wire:loading.remove wire:target="applyManualUpdate">
                                    Appliquer la mise a jour
                                </div>
                                <div wire:loading wire:target="applyManualUpdate" class="flex items-center gap-2">
                                    <span class="material-symbols-outlined animate-spin text-[18px]">refresh</span>
                                    Mise a jour en cours...
                                </div>
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('reload-page', () => {
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        });
    });
</script>
@endpush
