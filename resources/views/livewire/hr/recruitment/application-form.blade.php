<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postuler - {{ $jobPosition->title }} | Carrières</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto py-12 px-4">
        <!-- En-tête -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ $jobPosition->title }}</h1>
            <p class="text-gray-600 mt-2">
                {{ $jobPosition->department->name }}
                @if($jobPosition->location) • {{ $jobPosition->location }} @endif
                • {{ $jobPosition->type_label }}
            </p>
        </div>

        @if($submitted)
            <!-- Message de confirmation -->
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Candidature envoyée !</h2>
                <p class="text-gray-600 mb-6">
                    Merci pour votre candidature. Nous l'examinerons attentivement et reviendrons vers vous dans les plus brefs délais.
                </p>
                <a href="/" class="inline-block px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Retour à l'accueil
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Formulaire -->
                <div class="lg:col-span-2">
                    <form wire:submit="submit" class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-xl font-semibold mb-6">Postuler à cette offre</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                                <input type="text" wire:model="first_name" class="w-full border-gray-300 rounded-lg shadow-sm">
                                @error('first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                                <input type="text" wire:model="last_name" class="w-full border-gray-300 rounded-lg shadow-sm">
                                @error('last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                <input type="email" wire:model="email" class="w-full border-gray-300 rounded-lg shadow-sm">
                                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                                <input type="tel" wire:model="phone" class="w-full border-gray-300 rounded-lg shadow-sm"
                                    placeholder="+33 6 12 34 56 78">
                                @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">CV (PDF) *</label>
                            <input type="file" wire:model="resume" accept=".pdf,.doc,.docx"
                                class="w-full border border-gray-300 rounded-lg p-2">
                            @error('resume') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            <p class="text-xs text-gray-500 mt-1">Formats acceptés: PDF, DOC, DOCX. Max 5 Mo.</p>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lettre de motivation</label>
                            <textarea wire:model="cover_letter" rows="6" class="w-full border-gray-300 rounded-lg shadow-sm"
                                placeholder="Présentez-vous et expliquez votre motivation pour ce poste..."></textarea>
                            @error('cover_letter') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-6">
                            <label class="flex items-start">
                                <input type="checkbox" wire:model="consent" class="mt-1 rounded border-gray-300 text-primary-600">
                                <span class="ml-2 text-sm text-gray-600">
                                    J'accepte que mes données personnelles soient traitées dans le cadre de cette candidature,
                                    conformément à la politique de confidentialité. *
                                </span>
                            </label>
                            @error('consent') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="w-full px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>Envoyer ma candidature</span>
                            <span wire:loading>Envoi en cours...</span>
                        </button>
                    </form>
                </div>

                <!-- Description du poste -->
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="font-semibold mb-3">À propos du poste</h3>
                        <div class="text-sm text-gray-600 space-y-2">
                            @if($jobPosition->salary_range_min || $jobPosition->salary_range_max)
                                <p>
                                    <span class="font-medium">Salaire:</span>
                                    @if($jobPosition->salary_range_min && $jobPosition->salary_range_max)
                                        {{ number_format($jobPosition->salary_range_min, 0, ',', ' ') }} - {{ number_format($jobPosition->salary_range_max, 0, ',', ' ') }} FCFA/an
                                    @elseif($jobPosition->salary_range_min)
                                        À partir de {{ number_format($jobPosition->salary_range_min, 0, ',', ' ') }} FCFA/an
                                    @endif
                                </p>
                            @endif
                            @if($jobPosition->closes_at)
                                <p>
                                    <span class="font-medium">Date limite:</span>
                                    {{ $jobPosition->closes_at->format('d/m/Y') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if($jobPosition->description)
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="font-semibold mb-3">Description</h3>
                            <div class="text-sm text-gray-600">
                                {!! nl2br(e(Str::limit($jobPosition->description, 300))) !!}
                            </div>
                        </div>
                    @endif

                    @if($jobPosition->requirements)
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="font-semibold mb-3">Prérequis</h3>
                            <div class="text-sm text-gray-600">
                                {!! nl2br(e(Str::limit($jobPosition->requirements, 300))) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</body>
</html>
