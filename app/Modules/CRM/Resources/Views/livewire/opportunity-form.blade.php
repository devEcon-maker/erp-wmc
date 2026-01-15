<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h1 class="text-2xl font-bold text-white">
                {{ $isEdit ? 'Modifier l\'opportunite' : 'Nouvelle opportunite' }}
            </h1>
            <p class="text-text-secondary text-sm mt-1">Saisissez les informations cles de l'affaire.</p>
        </div>
        <a href="{{ route('crm.opportunities.index') }}"
            class="flex items-center gap-2 px-4 py-2 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Retour
        </a>
    </div>

    <!-- Formulaire -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6">
        <form wire:submit.prevent="save" class="space-y-6">
            <!-- Contact & Titre -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Contact / Client *</label>
                    <select wire:model="contact_id"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] text-white rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all {{ $errors->has('contact_id') ? 'border-red-500' : '' }}">
                        <option value="">Selectionner un contact...</option>
                        @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->display_name }}</option>
                        @endforeach
                    </select>
                    @error('contact_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Titre de l'affaire *</label>
                    <input type="text" wire:model="title" placeholder="ex: Contrat maintenance 2024"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] text-white rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all {{ $errors->has('title') ? 'border-red-500' : '' }}">
                    @error('title')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-text-secondary mb-2">Description</label>
                <textarea wire:model="description" rows="3" placeholder="Details de l'opportunite..."
                    class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] text-white rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all resize-none"></textarea>
            </div>

            <!-- Montant, Etape & Probabilite -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Montant (FCFA) *</label>
                    <div class="relative">
                        <input type="number" wire:model="amount" step="0.01" min="0"
                            class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] text-white rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all {{ $errors->has('amount') ? 'border-red-500' : '' }}">
                    </div>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Etape *</label>
                    <select wire:model.live="stage_id"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] text-white rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all {{ $errors->has('stage_id') ? 'border-red-500' : '' }}">
                        @foreach($stages as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                        @endforeach
                    </select>
                    @error('stage_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Probabilite (%) *</label>
                    <div class="relative">
                        <input type="number" wire:model="probability" min="0" max="100"
                            class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] text-white rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all pr-10 {{ $errors->has('probability') ? 'border-red-500' : '' }}">
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary">%</div>
                    </div>
                    @error('probability')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Date de cloture & Assignation -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Date de cloture prevue</label>
                    <input type="date" wire:model="expected_close_date"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] text-white rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Assigne a</label>
                    <select wire:model="assigned_to"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] text-white rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all">
                        <option value="">Non assigne</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Montant pondere (affichage) -->
            <div class="p-4 bg-surface-highlight rounded-xl border border-[#3a2e24]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-text-secondary">
                        <span class="material-symbols-outlined">calculate</span>
                        <span>Montant pondere</span>
                    </div>
                    <div class="text-xl font-bold text-primary">
                        {{ number_format(($amount ?: 0) * (($probability ?: 0) / 100), 0, ',', ' ') }} FCFA
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-6 border-t border-[#3a2e24]">
                <a href="{{ route('crm.opportunities.index') }}"
                    class="px-6 py-3 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight font-medium transition-colors">
                    Annuler
                </a>
                <button type="submit"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="flex items-center gap-2 px-6 py-3 rounded-xl bg-primary text-white font-medium hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20">
                    <span wire:loading.remove wire:target="save" class="material-symbols-outlined text-[18px]">save</span>
                    <span wire:loading wire:target="save" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
                    {{ $isEdit ? 'Mettre a jour' : 'Creer l\'opportunite' }}
                </button>
            </div>
        </form>
    </div>
</div>
