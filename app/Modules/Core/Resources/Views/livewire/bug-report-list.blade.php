<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight flex items-center gap-3">
                <span class="material-symbols-outlined text-primary text-3xl">bug_report</span>
                Signaler un probleme
            </h2>
            <p class="text-text-secondary text-sm mt-1">Signalez des bugs, proposez des ameliorations ou posez des questions.</p>
        </div>
        <div class="flex gap-3">
            <label class="flex items-center gap-2 text-text-secondary text-sm cursor-pointer">
                <input type="checkbox" wire:model.live="showMyReports" class="rounded bg-surface-highlight border-[#3a2e24] text-primary focus:ring-primary">
                Mes rapports uniquement
            </label>
            <x-ui.button wire:click="create" type="primary" icon="add">
                Nouveau rapport
            </x-ui.button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Total</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-text-secondary">description</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Ouverts</p>
                    <p class="text-2xl font-bold text-blue-400">{{ $stats['open'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-blue-500">folder_open</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">En cours</p>
                    <p class="text-2xl font-bold text-amber-400">{{ $stats['in_progress'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-amber-500">pending</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Resolus</p>
                    <p class="text-2xl font-bold text-green-400">{{ $stats['resolved'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-green-500">check_circle</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Bugs</p>
                    <p class="text-2xl font-bold text-red-400">{{ $stats['bugs'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-red-500">bug_report</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Ameliorations</p>
                    <p class="text-2xl font-bold text-indigo-400">{{ $stats['improvements'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-indigo-500">trending_up</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Critiques</p>
                    <p class="text-2xl font-bold text-rose-400">{{ $stats['critical'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-rose-500">priority_high</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <x-ui.card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.input wire:model.live.debounce.300ms="search" label="Recherche" placeholder="Titre, description, reference..." icon="search" />

            <x-ui.select wire:model.live="filterType" label="Type">
                <option value="">Tous les types</option>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="filterStatus" label="Statut">
                <option value="">Tous les statuts</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="filterPriority" label="Priorite">
                <option value="">Toutes les priorites</option>
                @foreach($priorities as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Reports Table -->
    <x-ui.card class="overflow-visible">
        <x-ui.table :headers="['Type', 'Priorite', 'Titre', 'Auteur', 'Statut', 'Date', 'Actions']">
            @forelse($reports as $report)
                <tr class="hover:bg-surface-highlight/50 transition-colors {{ $report->priority === 'critical' ? 'bg-red-500/5' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-{{ $report->type_color }}-500/20 text-{{ $report->type_color }}-400 text-sm">
                            <span class="material-symbols-outlined text-[16px]">{{ $report->type_icon }}</span>
                            {{ $report->type_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs bg-{{ $report->priority_color }}-500/20 text-{{ $report->priority_color }}-400">
                            @if($report->priority === 'critical')
                                <span class="material-symbols-outlined text-[14px] animate-pulse">priority_high</span>
                            @endif
                            {{ $report->priority_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="max-w-md">
                            <p class="text-sm font-medium text-white truncate">{{ $report->title }}</p>
                            <p class="text-xs text-text-secondary">{{ $report->reference }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <div class="size-8 rounded-full bg-surface-highlight flex items-center justify-center text-primary text-xs font-bold">
                                {{ substr($report->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div class="text-sm text-text-secondary">{{ $report->user->name ?? 'Utilisateur' }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-ui.badge :color="$report->status_color">
                            {{ $report->status_label }}
                        </x-ui.badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                        {{ $report->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex justify-end gap-1">
                            <button wire:click="view({{ $report->id }})"
                                class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-blue-400 transition-colors"
                                title="Voir">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </button>

                            @if($report->user_id === auth()->id() || $isAdmin)
                                <button wire:click="edit({{ $report->id }})"
                                    class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-orange-400 transition-colors"
                                    title="Modifier">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                            @endif

                            @if($isAdmin)
                                <button wire:click="openResponseModal({{ $report->id }})"
                                    class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-green-400 transition-colors"
                                    title="Repondre">
                                    <span class="material-symbols-outlined text-[20px]">reply</span>
                                </button>
                            @endif

                            @if($report->user_id === auth()->id() || $isAdmin)
                                <button wire:click="delete({{ $report->id }})"
                                    wire:confirm="Etes-vous sur de vouloir supprimer ce rapport ?"
                                    class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-red-400 transition-colors"
                                    title="Supprimer">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-text-secondary">
                        <div class="flex flex-col items-center justify-center">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">inbox</span>
                            <p>Aucun rapport trouve.</p>
                            <button wire:click="create" class="mt-4 text-primary hover:underline">
                                Creer un nouveau rapport
                            </button>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-ui.table>
        <div class="border-t border-[#3a2e24] p-4">
            {{ $reports->links() }}
        </div>
    </x-ui.card>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/70" wire:click="closeModal"></div>

                <div class="relative bg-surface-dark border border-[#3a2e24] rounded-2xl w-full max-w-2xl shadow-xl">
                    <form wire:submit="save">
                        <div class="px-6 py-4 border-b border-[#3a2e24]">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xl font-bold text-white">
                                    {{ $editingReport ? 'Modifier le rapport' : 'Nouveau rapport' }}
                                </h3>
                                <button type="button" wire:click="closeModal" class="text-text-secondary hover:text-white">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                        </div>

                        <div class="px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                            <div class="grid grid-cols-2 gap-4">
                                <x-ui.select wire:model="type" label="Type *" :error="$errors->first('type')">
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>

                                <x-ui.select wire:model="priority" label="Priorite *" :error="$errors->first('priority')">
                                    @foreach($priorities as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>

                            <x-ui.input wire:model="title" label="Titre *" placeholder="Decrivez brievement le probleme" :error="$errors->first('title')" />

                            <x-ui.textarea wire:model="description" label="Description *" rows="4" placeholder="Decrivez en detail le probleme ou votre suggestion..." :error="$errors->first('description')" />

                            <x-ui.input wire:model="page_url" label="URL de la page concernee" placeholder="http://..." :error="$errors->first('page_url')" />

                            @if($type === 'bug')
                                <x-ui.textarea wire:model="steps_to_reproduce" label="Etapes pour reproduire" rows="3" placeholder="1. Aller sur...&#10;2. Cliquer sur...&#10;3. Observer l'erreur..." />

                                <div class="grid grid-cols-2 gap-4">
                                    <x-ui.textarea wire:model="expected_behavior" label="Comportement attendu" rows="2" placeholder="Que devrait-il se passer ?" />
                                    <x-ui.textarea wire:model="actual_behavior" label="Comportement actuel" rows="2" placeholder="Que se passe-t-il reellement ?" />
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-text-secondary mb-2">Capture d'ecran (optionnel)</label>
                                <div class="flex items-center justify-center w-full">
                                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-[#3a2e24] rounded-xl cursor-pointer hover:bg-surface-highlight transition-colors">
                                        @if($screenshot)
                                            <img src="{{ $screenshot->temporaryUrl() }}" class="h-28 object-contain rounded-lg" alt="Preview">
                                        @else
                                            <div class="flex flex-col items-center justify-center">
                                                <span class="material-symbols-outlined text-3xl text-text-secondary mb-2">cloud_upload</span>
                                                <p class="text-sm text-text-secondary">Cliquez pour telecharger une image</p>
                                                <p class="text-xs text-text-secondary mt-1">PNG, JPG jusqu'a 5MB</p>
                                            </div>
                                        @endif
                                        <input type="file" wire:model="screenshot" class="hidden" accept="image/*">
                                    </label>
                                </div>
                                @error('screenshot') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-[#3a2e24] flex justify-end gap-3">
                            <x-ui.button type="secondary" wire:click="closeModal">
                                Annuler
                            </x-ui.button>
                            <x-ui.button type="primary" :submit="true">
                                <span wire:loading.remove wire:target="save">
                                    {{ $editingReport ? 'Mettre a jour' : 'Soumettre le rapport' }}
                                </span>
                                <span wire:loading wire:target="save" class="flex items-center gap-2">
                                    <span class="material-symbols-outlined animate-spin text-[18px]">refresh</span>
                                    Enregistrement...
                                </span>
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- View Modal -->
    @if($showViewModal && $viewingReport)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/70" wire:click="closeModal"></div>

                <div class="relative bg-surface-dark border border-[#3a2e24] rounded-2xl w-full max-w-2xl shadow-xl">
                    <div class="px-6 py-4 border-b border-[#3a2e24]">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-2xl text-{{ $viewingReport->type_color }}-400">{{ $viewingReport->type_icon }}</span>
                                <div>
                                    <h3 class="text-xl font-bold text-white">{{ $viewingReport->title }}</h3>
                                    <p class="text-xs text-text-secondary">{{ $viewingReport->reference }}</p>
                                </div>
                            </div>
                            <button type="button" wire:click="closeModal" class="text-text-secondary hover:text-white">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                        <!-- Meta Info -->
                        <div class="flex flex-wrap gap-3">
                            <x-ui.badge :color="$viewingReport->type_color">{{ $viewingReport->type_label }}</x-ui.badge>
                            <x-ui.badge :color="$viewingReport->priority_color">{{ $viewingReport->priority_label }}</x-ui.badge>
                            <x-ui.badge :color="$viewingReport->status_color">{{ $viewingReport->status_label }}</x-ui.badge>
                        </div>

                        <!-- Author & Date -->
                        <div class="bg-surface-highlight rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold">
                                        {{ substr($viewingReport->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-white">{{ $viewingReport->user->name ?? 'Utilisateur' }}</p>
                                        <p class="text-xs text-text-secondary">{{ $viewingReport->created_at->format('d/m/Y a H:i') }}</p>
                                    </div>
                                </div>
                                @if($viewingReport->page_url)
                                    <a href="{{ $viewingReport->page_url }}" target="_blank" class="text-primary hover:underline text-sm flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                                        Voir la page
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <h4 class="text-sm font-bold text-white mb-2">Description</h4>
                            <p class="text-text-secondary whitespace-pre-wrap">{{ $viewingReport->description }}</p>
                        </div>

                        @if($viewingReport->steps_to_reproduce)
                            <div>
                                <h4 class="text-sm font-bold text-white mb-2">Etapes pour reproduire</h4>
                                <p class="text-text-secondary whitespace-pre-wrap">{{ $viewingReport->steps_to_reproduce }}</p>
                            </div>
                        @endif

                        @if($viewingReport->expected_behavior || $viewingReport->actual_behavior)
                            <div class="grid grid-cols-2 gap-4">
                                @if($viewingReport->expected_behavior)
                                    <div>
                                        <h4 class="text-sm font-bold text-white mb-2">Comportement attendu</h4>
                                        <p class="text-text-secondary text-sm">{{ $viewingReport->expected_behavior }}</p>
                                    </div>
                                @endif
                                @if($viewingReport->actual_behavior)
                                    <div>
                                        <h4 class="text-sm font-bold text-white mb-2">Comportement actuel</h4>
                                        <p class="text-text-secondary text-sm">{{ $viewingReport->actual_behavior }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($viewingReport->screenshot_path)
                            <div>
                                <h4 class="text-sm font-bold text-white mb-2">Capture d'ecran</h4>
                                <a href="{{ Storage::url($viewingReport->screenshot_path) }}" target="_blank">
                                    <img src="{{ Storage::url($viewingReport->screenshot_path) }}" class="max-h-64 rounded-lg border border-[#3a2e24]" alt="Screenshot">
                                </a>
                            </div>
                        @endif

                        @if($viewingReport->admin_response)
                            <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-green-400">admin_panel_settings</span>
                                    <h4 class="text-sm font-bold text-green-400">Reponse de l'administrateur</h4>
                                </div>
                                <p class="text-text-secondary whitespace-pre-wrap">{{ $viewingReport->admin_response }}</p>
                                @if($viewingReport->resolver)
                                    <p class="text-xs text-text-secondary mt-2">
                                        Par {{ $viewingReport->resolver->name }} le {{ $viewingReport->resolved_at?->format('d/m/Y a H:i') }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="px-6 py-4 border-t border-[#3a2e24] flex justify-end">
                        <x-ui.button type="secondary" wire:click="closeModal">
                            Fermer
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Admin Response Modal -->
    @if($showResponseModal && $respondingReport)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/70" wire:click="closeModal"></div>

                <div class="relative bg-surface-dark border border-[#3a2e24] rounded-2xl w-full max-w-lg shadow-xl">
                    <div class="px-6 py-4 border-b border-[#3a2e24]">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-white">Repondre au rapport</h3>
                            <button type="button" wire:click="closeModal" class="text-text-secondary hover:text-white">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        <!-- Report Info -->
                        <div class="bg-surface-highlight rounded-lg p-4">
                            <p class="text-sm font-medium text-white">{{ $respondingReport->title }}</p>
                            <p class="text-xs text-text-secondary mt-1">{{ $respondingReport->reference }} - Par {{ $respondingReport->user->name ?? 'Utilisateur' }}</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-text-secondary mb-2">Statut</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach(['open' => 'Ouvert', 'in_progress' => 'En cours', 'resolved' => 'Resolu', 'closed' => 'Ferme', 'wont_fix' => 'Ne sera pas corrige'] as $key => $label)
                                    <label class="flex items-center p-3 border border-[#3a2e24] rounded-lg cursor-pointer hover:bg-surface-highlight transition-colors {{ $response_status === $key ? 'border-primary bg-primary/10' : '' }}">
                                        <input type="radio" wire:model="response_status" value="{{ $key }}" class="mr-3 text-primary focus:ring-primary">
                                        <span class="text-text-secondary text-sm">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Response -->
                        <x-ui.textarea wire:model="admin_response" label="Reponse / Commentaire" rows="4" placeholder="Votre reponse a l'utilisateur..." />
                    </div>

                    <div class="px-6 py-4 border-t border-[#3a2e24] flex justify-end gap-3">
                        <x-ui.button type="secondary" wire:click="closeModal">
                            Annuler
                        </x-ui.button>
                        <x-ui.button type="primary" wire:click="saveResponse">
                            Enregistrer la reponse
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
