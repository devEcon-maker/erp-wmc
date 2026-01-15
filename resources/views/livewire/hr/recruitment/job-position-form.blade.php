<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">
            {{ $jobPosition ? 'Modifier l\'offre' : 'Nouvelle offre d\'emploi' }}
        </h1>
        <p class="text-text-secondary mt-1">Créez ou modifiez une offre d'emploi pour votre équipe.</p>
    </div>

    <form wire:submit="save">
        <!-- Informations du poste -->
        <div class="bg-surface-dark border border-white/10 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4">Informations du poste</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-text-secondary mb-1">Titre du poste *</label>
                    <input type="text" wire:model="title"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Ex: Développeur Full Stack">
                    @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">Département *</label>
                    <select wire:model="department_id"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Sélectionner...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">Type de contrat *</label>
                    <select wire:model="type"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 focus:ring-primary-500 focus:border-primary-500">
                        <option value="full_time">Temps plein (CDI)</option>
                        <option value="part_time">Temps partiel</option>
                        <option value="contract">CDD</option>
                        <option value="internship">Stage</option>
                    </select>
                    @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">Lieu de travail</label>
                    <input type="text" wire:model="location"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Ex: Paris, Télétravail...">
                    @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">Date de clôture</label>
                    <input type="date" wire:model="closes_at"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 focus:ring-primary-500 focus:border-primary-500">
                    @error('closes_at') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">Salaire minimum (FCFA/an)</label>
                    <input type="number" wire:model="salary_range_min"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="35000">
                    @error('salary_range_min') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">Salaire maximum (FCFA/an)</label>
                    <input type="number" wire:model="salary_range_max"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="50000">
                    @error('salary_range_max') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-text-secondary mb-1">Description du poste</label>
                <textarea wire:model="description" rows="6"
                    class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Décrivez les missions et responsabilités du poste..."></textarea>
                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-text-secondary mb-1">Prérequis</label>
                <textarea wire:model="requirements" rows="4"
                    class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Listez les compétences et qualifications requises..."></textarea>
                @error('requirements') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Publication -->
        <div class="bg-surface-dark border border-white/10 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4">Publication</h2>
            <div class="flex items-center space-x-6">
                <label class="flex items-center cursor-pointer">
                    <input type="radio" wire:model="status" value="draft"
                        class="w-4 h-4 text-primary bg-surface-dark border-white/20 focus:ring-primary-500">
                    <span class="ml-2 text-white">Brouillon</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="radio" wire:model="status" value="published"
                        class="w-4 h-4 text-primary bg-surface-dark border-white/20 focus:ring-primary-500">
                    <span class="ml-2 text-white">Publier maintenant</span>
                </label>
            </div>
            @if($status === 'published')
                <p class="mt-3 text-sm text-text-secondary">
                    <span class="material-symbols-outlined text-[16px] align-middle mr-1">public</span>
                    L'offre sera visible publiquement sur la page carrières.
                </p>
            @else
                <p class="mt-3 text-sm text-text-secondary">
                    <span class="material-symbols-outlined text-[16px] align-middle mr-1">visibility_off</span>
                    L'offre restera en brouillon et ne sera pas visible publiquement.
                </p>
            @endif
            @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Actions -->
        <div class="flex justify-between">
            <a href="{{ route('hr.recruitment.positions.index') }}"
                class="px-4 py-2 border border-white/10 text-text-secondary rounded-lg hover:bg-white/5">
                Annuler
            </a>
            <button type="submit"
                class="px-6 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg">
                {{ $jobPosition ? 'Mettre à jour' : 'Créer l\'offre' }}
            </button>
        </div>
    </form>
</div>
