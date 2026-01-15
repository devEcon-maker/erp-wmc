<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Nouvelle Demande de Conge</h1>
        <p class="text-text-secondary mt-1">Remplissez le formulaire ci-dessous pour soumettre votre demande.</p>
    </div>

    <div class="max-w-2xl">
        <div class="bg-surface-dark border border-white/10 rounded-lg p-6">
            <form wire:submit.prevent="save" class="space-y-4">
                <!-- Leave Type -->
                <div>
                    <label for="leave_type_id" class="block text-sm font-medium text-text-secondary mb-1">Type de conge *</label>
                    <select wire:model.live="leave_type_id" id="leave_type_id"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white focus:ring-primary-500 focus:border-primary-500 py-2 px-3">
                        <option value="">Selectionner...</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">
                                {{ $type->name }}
                                @if($type->requires_justification)
                                    (Justificatif requis)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('leave_type_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-text-secondary mb-1">Date de debut *</label>
                        <input type="date" wire:model.live="start_date" id="start_date"
                            class="w-full bg-surface-dark border border-white/10 rounded-lg text-white focus:ring-primary-500 focus:border-primary-500 py-2 px-3" />
                        @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-text-secondary mb-1">Date de fin *</label>
                        <input type="date" wire:model.live="end_date" id="end_date"
                            class="w-full bg-surface-dark border border-white/10 rounded-lg text-white focus:ring-primary-500 focus:border-primary-500 py-2 px-3" />
                        @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Duration Display -->
                @if($days_count > 0)
                    <div class="p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                        <p class="text-blue-400 text-sm flex items-center">
                            <span class="material-symbols-outlined text-[18px] mr-2">schedule</span>
                            Duree estimee : <span class="font-bold ml-1">{{ $days_count }} jours ouvres</span>
                        </p>
                    </div>
                @endif

                <!-- Reason -->
                <div>
                    <label for="reason" class="block text-sm font-medium text-text-secondary mb-1">Motif (optionnel)</label>
                    <textarea wire:model="reason" id="reason" rows="3"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white focus:ring-primary-500 focus:border-primary-500 py-2 px-3"
                        placeholder="Precisez le motif de votre demande..."></textarea>
                    @error('reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Justificatif - Conditionnel -->
                @if($requiresJustification)
                    <div class="p-4 bg-amber-500/10 border border-amber-500/30 rounded-lg">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="material-symbols-outlined text-amber-400">warning</span>
                            <div>
                                <p class="text-amber-400 font-medium">Justificatif medical obligatoire</p>
                                <p class="text-text-secondary text-sm mt-1">
                                    Pour ce type de conge, vous devez fournir un justificatif medical (certificat, ordonnance, etc.).
                                </p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-text-secondary mb-2">
                                Telecharger le justificatif * (PDF, JPG, PNG - Max 5 Mo)
                            </label>

                            <div class="relative">
                                <input type="file" wire:model="justification" accept=".pdf,.jpg,.jpeg,.png"
                                    class="block w-full text-sm text-text-secondary
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-lg file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-amber-500/20 file:text-amber-400
                                        hover:file:bg-amber-500/30
                                        cursor-pointer" />
                                <div wire:loading wire:target="justification" class="absolute inset-0 flex items-center justify-center bg-surface-dark/80 rounded-lg">
                                    <span class="material-symbols-outlined animate-spin text-amber-400">refresh</span>
                                    <span class="ml-2 text-sm text-amber-400">Chargement...</span>
                                </div>
                            </div>

                            @error('justification')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            @if($justification)
                                <div class="flex items-center gap-2 p-2 bg-green-500/10 rounded-lg mt-2">
                                    <span class="material-symbols-outlined text-green-400 text-[18px]">check_circle</span>
                                    <span class="text-sm text-green-400">Fichier selectionne: {{ $justification->getClientOriginalName() }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="flex justify-end pt-4 border-t border-white/10">
                    <a href="{{ route('hr.leaves.index') }}" class="px-4 py-2 border border-white/10 text-text-secondary rounded-lg hover:bg-white/5 mr-3">
                        Annuler
                    </a>
                    <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg flex items-center gap-2">
                        <span wire:loading.remove wire:target="save">Soumettre la demande</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <span class="material-symbols-outlined animate-spin text-[18px]">refresh</span>
                            Envoi...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
