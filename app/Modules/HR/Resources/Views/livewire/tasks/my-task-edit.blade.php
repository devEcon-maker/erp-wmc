<div class="p-6 max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('hr.my-tasks.show', $task) }}" class="text-text-secondary hover:text-white text-sm flex items-center gap-1 mb-2 transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Retour a la tache
        </a>
        <h1 class="text-2xl font-bold text-white">Modifier la tache</h1>
        <p class="text-text-secondary text-sm">Modifiez les details de votre tache</p>
    </div>

    <form wire:submit="save" class="space-y-6">
        <!-- Titre -->
        <div class="bg-surface-dark rounded-xl p-6 border border-[#3a2e24]">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Titre *</label>
                    <input type="text" wire:model="title"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                        placeholder="Titre de la tache">
                    @error('title') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Description</label>
                    <textarea wire:model="description" rows="4"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white placeholder-text-secondary focus:outline-none focus:border-primary resize-none"
                        placeholder="Decrivez la tache en detail..."></textarea>
                </div>
            </div>
        </div>

        <!-- Statut et Priorite -->
        <div class="bg-surface-dark rounded-xl p-6 border border-[#3a2e24]">
            <h3 class="text-white font-bold mb-4">Statut et priorite</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Statut *</label>
                    <select wire:model="status_id"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                    @error('status_id') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Priorite *</label>
                    <select wire:model="priority"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                        @foreach($priorities as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Planification -->
        <div class="bg-surface-dark rounded-xl p-6 border border-[#3a2e24]">
            <h3 class="text-white font-bold mb-4">Planification</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Date d'echeance</label>
                    <input type="date" wire:model="due_date"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Heures estimees</label>
                    <input type="number" wire:model="estimated_hours" min="1"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                        placeholder="Ex: 8">
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-surface-dark rounded-xl p-6 border border-[#3a2e24]">
            <h3 class="text-white font-bold mb-4">Notes</h3>
            <textarea wire:model="notes" rows="3"
                class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white placeholder-text-secondary focus:outline-none focus:border-primary resize-none"
                placeholder="Notes supplementaires..."></textarea>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('hr.my-tasks.show', $task) }}" class="px-6 py-3 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight font-medium transition-colors">
                Annuler
            </a>
            <button type="submit" class="px-6 py-3 rounded-xl bg-primary hover:bg-primary/90 text-white font-bold transition-colors">
                Enregistrer
            </button>
        </div>
    </form>
</div>
