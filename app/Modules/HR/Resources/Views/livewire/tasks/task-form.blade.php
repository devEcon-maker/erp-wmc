<div class="p-6 max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('hr.tasks.index') }}" class="text-text-secondary hover:text-white text-sm flex items-center gap-1 mb-2 transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Retour aux taches
        </a>
        <h1 class="text-2xl font-bold text-white">{{ $isEdit ? 'Modifier la tache' : 'Nouvelle tache' }}</h1>
    </div>

    <!-- Form -->
    <form wire:submit="save" class="space-y-6">
        <div class="bg-surface-dark rounded-xl p-6 border border-[#3a2e24] space-y-6">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-text-secondary mb-2">Titre *</label>
                <input type="text" id="title" wire:model="title"
                    class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                    placeholder="Ex: Preparer le rapport mensuel">
                @error('title') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-text-secondary mb-2">Description</label>
                <textarea id="description" wire:model="description" rows="4"
                    class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white placeholder-text-secondary focus:outline-none focus:border-primary resize-none"
                    placeholder="Details de la tache..."></textarea>
                @error('description') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Employee & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-text-secondary mb-2">Employe *</label>
                    <select id="employee_id" wire:model="employee_id"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                        <option value="">Selectionner un employe</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->job_title }})</option>
                        @endforeach
                    </select>
                    @error('employee_id') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="status_id" class="block text-sm font-medium text-text-secondary mb-2">Statut *</label>
                    <select id="status_id" wire:model="status_id"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                        <option value="">Selectionner un statut</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                    @error('status_id') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Priority & Due Date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="priority" class="block text-sm font-medium text-text-secondary mb-2">Priorite *</label>
                    <select id="priority" wire:model="priority"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                        @foreach($priorities as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('priority') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-medium text-text-secondary mb-2">Date d'echeance</label>
                    <input type="date" id="due_date" wire:model="due_date"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                    @error('due_date') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Estimated Hours -->
            <div>
                <label for="estimated_hours" class="block text-sm font-medium text-text-secondary mb-2">Heures estimees</label>
                <input type="number" id="estimated_hours" wire:model="estimated_hours" min="1"
                    class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                    placeholder="Ex: 8">
                @error('estimated_hours') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-text-secondary mb-2">Notes internes</label>
                <textarea id="notes" wire:model="notes" rows="3"
                    class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white placeholder-text-secondary focus:outline-none focus:border-primary resize-none"
                    placeholder="Notes ou commentaires..."></textarea>
                @error('notes') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('hr.tasks.index') }}" class="px-6 py-3 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight font-medium transition-colors">
                Annuler
            </a>
            <button type="submit" class="px-6 py-3 rounded-xl bg-primary hover:bg-primary/90 text-white font-bold transition-colors shadow-lg shadow-primary/20">
                {{ $isEdit ? 'Mettre a jour' : 'Creer la tache' }}
            </button>
        </div>
    </form>
</div>
