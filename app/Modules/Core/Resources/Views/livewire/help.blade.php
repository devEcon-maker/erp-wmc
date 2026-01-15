<div class="space-y-8" x-data="{ activeSection: 'overview' }">
    <!-- Header Hero -->
    <div class="bg-surface-dark border border-[#3a2e24] p-8 rounded-2xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <h2 class="text-3xl font-bold text-white tracking-tight mb-2">
                Centre d'Aide & Documentation
            </h2>
            <p class="text-lg text-text-secondary">
                Bienvenue dans votre guide complet. Sélectionnez un module ci-dessous pour apprendre à maîtriser chaque
                fonctionnalité de votre ERP, étape par étape.
            </p>
        </div>
        <div
            class="absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-primary/10 to-transparent pointer-events-none">
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-3">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-4 sticky top-24 shadow-lg">
                <nav class="space-y-2">
                    <button @click="activeSection = 'overview'"
                        :class="activeSection === 'overview' ? 'bg-surface-highlight text-white ring-1 ring-[#3a2e24]' : 'text-text-secondary hover:bg-surface-highlight hover:text-white'"
                        class="w-full flex items-center gap-3 px-4 py-4 rounded-xl text-left transition-all duration-200">
                        <div class="p-2 rounded-lg"
                            :class="activeSection === 'overview' ? 'bg-primary text-white' : 'bg-surface-dark text-text-secondary'">
                            <span class="material-symbols-outlined text-[20px]">dashboard</span>
                        </div>
                        <span class="font-medium text-sm">Vue d'ensemble</span>
                    </button>

                    <div class="h-px bg-[#3a2e24] my-2"></div>

                    <button @click="activeSection = 'crm'"
                        :class="activeSection === 'crm' ? 'bg-surface-highlight text-white ring-1 ring-[#3a2e24]' : 'text-text-secondary hover:bg-surface-highlight hover:text-white'"
                        class="w-full flex items-center gap-3 px-4 py-4 rounded-xl text-left transition-all duration-200">
                        <div class="p-2 rounded-lg"
                            :class="activeSection === 'crm' ? 'bg-indigo-500 text-white' : 'bg-surface-dark text-indigo-400'">
                            <span class="material-symbols-outlined text-[20px]">rocket_launch</span>
                        </div>
                        <span class="font-medium text-sm">CRM & Ventes</span>
                    </button>

                    <button @click="activeSection = 'hr'"
                        :class="activeSection === 'hr' ? 'bg-surface-highlight text-white ring-1 ring-[#3a2e24]' : 'text-text-secondary hover:bg-surface-highlight hover:text-white'"
                        class="w-full flex items-center gap-3 px-4 py-4 rounded-xl text-left transition-all duration-200">
                        <div class="p-2 rounded-lg"
                            :class="activeSection === 'hr' ? 'bg-pink-500 text-white' : 'bg-surface-dark text-pink-400'">
                            <span class="material-symbols-outlined text-[20px]">groups</span>
                        </div>
                        <span class="font-medium text-sm">Ressources Humaines</span>
                    </button>

                    <button @click="activeSection = 'inventory'"
                        :class="activeSection === 'inventory' ? 'bg-surface-highlight text-white ring-1 ring-[#3a2e24]' : 'text-text-secondary hover:bg-surface-highlight hover:text-white'"
                        class="w-full flex items-center gap-3 px-4 py-4 rounded-xl text-left transition-all duration-200">
                        <div class="p-2 rounded-lg"
                            :class="activeSection === 'inventory' ? 'bg-amber-500 text-white' : 'bg-surface-dark text-amber-500'">
                            <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                        </div>
                        <span class="font-medium text-sm">Stocks & Achats</span>
                    </button>

                    <button @click="activeSection = 'finance'"
                        :class="activeSection === 'finance' ? 'bg-surface-highlight text-white ring-1 ring-[#3a2e24]' : 'text-text-secondary hover:bg-surface-highlight hover:text-white'"
                        class="w-full flex items-center gap-3 px-4 py-4 rounded-xl text-left transition-all duration-200">
                        <div class="p-2 rounded-lg"
                            :class="activeSection === 'finance' ? 'bg-emerald-500 text-white' : 'bg-surface-dark text-emerald-500'">
                            <span class="material-symbols-outlined text-[20px]">payments</span>
                        </div>
                        <span class="font-medium text-sm">Finance</span>
                    </button>

                    <button @click="activeSection = 'productivity'"
                        :class="activeSection === 'productivity' ? 'bg-surface-highlight text-white ring-1 ring-[#3a2e24]' : 'text-text-secondary hover:bg-surface-highlight hover:text-white'"
                        class="w-full flex items-center gap-3 px-4 py-4 rounded-xl text-left transition-all duration-200">
                        <div class="p-2 rounded-lg"
                            :class="activeSection === 'productivity' ? 'bg-blue-500 text-white' : 'bg-surface-dark text-blue-500'">
                            <span class="material-symbols-outlined text-[20px]">check_circle</span>
                        </div>
                        <span class="font-medium text-sm">Productivité</span>
                    </button>
                </nav>
            </div>
        </div>

        <!-- Content Area -->
        <div class="lg:col-span-9 space-y-8">

            <!-- OVERVIEW -->
            <div x-show="activeSection === 'overview'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-8 animate-fade-in-up">

                <section class="space-y-4">
                    <h3 class="text-2xl font-bold text-white border-b border-[#3a2e24] pb-4">Premiers Pas</h3>
                    <div class="bg-surface-dark border border-[#3a2e24] p-6 rounded-xl prose prose-invert max-w-none">
                        <p class="text-text-secondary leading-relaxed">
                            L'ERP WMC centralise toutes les opérations de votre entreprise.
                            La barre latérale gauche (menu principal) vous permet de naviguer entre les différents
                            modules.
                            En haut de chaque page, vous retrouverez toujours les mêmes outils essentiels.
                        </p>
                    </div>
                </section>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.card>
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-surface-highlight rounded-xl">
                                <span class="material-symbols-outlined text-primary text-2xl">search</span>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-white mb-2">Recherche Globale</h4>
                                <p class="text-text-secondary text-sm leading-relaxed mb-4">
                                    Située en haut à droite, la barre de recherche est votre outil le plus puissant.
                                    Elle vous permet de trouver instantanément :
                                </p>
                                <ul class="space-y-2 text-sm text-text-secondary">
                                    <li class="flex items-center gap-2"><span
                                            class="w-1.5 h-1.5 rounded-full bg-primary"></span>Un client ou un contact
                                    </li>
                                    <li class="flex items-center gap-2"><span
                                            class="w-1.5 h-1.5 rounded-full bg-primary"></span>Une facture par son
                                        numéro</li>
                                    <li class="flex items-center gap-2"><span
                                            class="w-1.5 h-1.5 rounded-full bg-primary"></span>Un produit en stock</li>
                                </ul>
                            </div>
                        </div>
                    </x-ui.card>

                    <x-ui.card>
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-surface-highlight rounded-xl">
                                <span class="material-symbols-outlined text-primary text-2xl">notifications</span>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-white mb-2">Notifications</h4>
                                <p class="text-text-secondary text-sm leading-relaxed mb-4">
                                    La cloche vous alerte en temps réel. Un badge rouge indique le nombre de
                                    notifications non lues.
                                </p>
                                <ul class="space-y-2 text-sm text-text-secondary">
                                    <li class="flex items-center gap-2"><span
                                            class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Stock faible sur un
                                        produit</li>
                                    <li class="flex items-center gap-2"><span
                                            class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Validation de vos
                                        congés</li>
                                    <li class="flex items-center gap-2"><span
                                            class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>Nouvelle commande
                                        assignée</li>
                                </ul>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>

            <!-- CRM -->
            <div x-show="activeSection === 'crm'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-8" style="display: none;">

                <div class="flex items-center gap-4 border-b border-[#3a2e24] pb-6">
                    <div class="p-3 bg-indigo-500/20 rounded-xl">
                        <span class="material-symbols-outlined text-indigo-400 text-3xl">rocket_launch</span>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white">Gestion de la Relation Client (CRM)</h3>
                        <p class="text-text-secondary">Gérez tout le cycle de vente, du premier contact à la facturation
                            finale.</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6">
                        <h4 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-sm font-bold">1</span>
                            Le cycle de vente simple
                        </h4>
                        <div class="relative pl-4 border-l-2 border-indigo-500/20 space-y-6">
                            <div class="relative">
                                <span
                                    class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-indigo-500 ring-4 ring-surface-dark"></span>
                                <h5 class="text-white font-medium mb-1">Créer un Contact</h5>
                                <p class="text-sm text-text-secondary">Allez dans <span
                                        class="text-white font-medium">CRM > Contacts</span>. Cliquez sur "Nouveau".
                                    Vous pouvez créer une entreprise ou un particulier. C'est le point de départ de
                                    toute interaction.</p>
                            </div>

                            <div class="relative">
                                <span
                                    class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-indigo-500 ring-4 ring-surface-dark"></span>
                                <h5 class="text-white font-medium mb-1">Créer une Opportunité</h5>
                                <p class="text-sm text-text-secondary">Dans <span class="text-white font-medium">CRM >
                                        Opportunités</span>, vous visualisez votre "Pipeline". Chaque colonne représente
                                    une étape (Qualification, Proposition, Négociation...). Glissez-déposez les cartes
                                    pour faire avancer vos affaires.</p>
                            </div>

                            <div class="relative">
                                <span
                                    class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-indigo-500 ring-4 ring-surface-dark"></span>
                                <h5 class="text-white font-medium mb-1">Envoyer un Devis (Proposition)</h5>
                                <p class="text-sm text-text-secondary">Depuis une opportunité ou un contact, créez une
                                    Proposition. Ajoutez vos produits/services. Le système génère automatiquement un PDF
                                    professionnel que vous pouvez envoyer par email directement.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-surface-dark border border-[#3a2e24] p-6 rounded-xl">
                            <h4 class="font-bold text-white mb-2">Commandes Clients</h4>
                            <p class="text-sm text-text-secondary leading-relaxed">
                                Une fois le devis accepté, transformez-le en Commande en un clic.
                                La commande a un impact direct sur vos stocks (réservation des produits).
                            </p>
                        </div>
                        <div class="bg-surface-dark border border-[#3a2e24] p-6 rounded-xl">
                            <h4 class="font-bold text-white mb-2">Contrats & Abonnements</h4>
                            <p class="text-sm text-text-secondary leading-relaxed">
                                Pour les services récurrents, utilisez le module Contrats.
                                Définissez la fréquence (mensuelle, annuelle), et le système générera les factures
                                automatiquement.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HR -->
            <div x-show="activeSection === 'hr'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-8" style="display: none;">

                <div class="flex items-center gap-4 border-b border-[#3a2e24] pb-6">
                    <div class="p-3 bg-pink-500/20 rounded-xl">
                        <span class="material-symbols-outlined text-pink-400 text-3xl">groups</span>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white">Ressources Humaines</h3>
                        <p class="text-text-secondary">Gérez votre vie en entreprise : congés, dépenses, paie et
                            recrutement.</p>
                    </div>
                </div>

                <!-- SECTION 1: Employés & Organisation -->
                <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6 mb-6">
                    <h4 class="text-lg font-bold text-white mb-4">1. Employés & Organisation</h4>
                    <p class="text-sm text-text-secondary mb-4">
                        La base de données RH centralise toutes les informations de votre personnel.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-surface-highlight rounded-xl border border-[#3a2e24]">
                            <h5 class="font-bold text-white mb-1">Fiches Employés</h5>
                            <p class="text-xs text-text-secondary">Contient : Contrat, salaire de base (en FCFA),
                                coordonnées bancaires, date d'entrée, poste et manager.</p>
                        </div>
                        <div class="p-4 bg-surface-highlight rounded-xl border border-[#3a2e24]">
                            <h5 class="font-bold text-white mb-1">Organigramme</h5>
                            <p class="text-xs text-text-secondary">Généré automatiquement à partir des liens
                                hiérarchiques. Permet de visualiser la structure de l'entreprise.</p>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: GESTION DE LA PAIE (NOUVEAU) -->
                <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6 relative overflow-hidden mb-6">
                    <div class="absolute top-0 right-0 p-4 opacity-50">
                        <span class="material-symbols-outlined text-[100px] text-pink-500/10">payments</span>
                    </div>
                    <div class="relative z-10">
                        <h4 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-pink-400">payments</span>
                            2. Gestion de la Paie & Avances
                        </h4>

                        <div class="space-y-6">
                            <div>
                                <h5 class="text-white font-bold mb-2">Processus de Paie</h5>
                                <ol class="space-y-3 text-sm text-text-secondary list-decimal list-inside">
                                    <li><strong class="text-white">Périodes de Paie :</strong> Les gestionnaires RH
                                        ouvrent une période (ex: "Paie Mensuelle Juin 2026").</li>
                                    <li><strong class="text-white">Génération :</strong> Le système calcule
                                        automatiquement les bulletins en prenant en compte le salaire de base et les
                                        congés.</li>
                                    <li><strong class="text-white">Validation :</strong> Une fois vérifiée, la période
                                        est validée et les bulletins sont disponibles pour les employés.</li>
                                </ol>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <ul class="space-y-2 text-sm text-text-secondary">
                                    <li class="flex items-start gap-2">
                                        <span
                                            class="material-symbols-outlined text-green-400 text-sm mt-0.5">add_circle</span>
                                        <div>
                                            <strong class="text-white block">Salaires & Primes</strong>
                                            Définis dans la fiche employé (Montant en FCFA).
                                        </div>
                                    </li>
                                </ul>
                                <ul class="space-y-2 text-sm text-text-secondary">
                                    <li class="flex items-start gap-2">
                                        <span
                                            class="material-symbols-outlined text-blue-400 text-sm mt-0.5">price_check</span>
                                        <div>
                                            <strong class="text-white block">Avances sur Salaire</strong>
                                            Les employés peuvent demander une avance via leur portail. Une fois
                                            approuvée, elle est automatiquement déduite du prochain bulletin.
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: Congés -->
                <div class="space-y-4 pt-6 border-t border-[#3a2e24]">
                    <h4 class="text-xl font-bold text-white">3. Congés & Absences</h4>
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-hidden p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div class="p-3 bg-surface-highlight rounded-lg border-l-4 border-blue-500">
                                <span class="block text-xs font-bold text-white uppercase">CP</span>
                                <span class="tex-sm text-text-secondary">Congés Payés (25j/an)</span>
                            </div>
                            <div class="p-3 bg-surface-highlight rounded-lg border-l-4 border-purple-500">
                                <span class="block text-xs font-bold text-white uppercase">RTT</span>
                                <span class="tex-sm text-text-secondary">Réduction du Temps de Travail</span>
                            </div>
                        </div>

                        <h5 class="font-bold text-white">Comment poser un congé ?</h5>
                        <ol class="space-y-3 text-sm text-text-secondary list-decimal list-inside">
                            <li>Allez dans le menu <span class="text-white font-medium">RH > Mes Congés</span>.</li>
                            <li>Cliquez sur le bouton <span class="text-primary font-bold">Nouvelle Demande</span>.</li>
                            <li>Sélectionnez le type et la période.</li>
                            <li>Validez. Votre manager recevra une notification pour validation.</li>
                        </ol>
                    </div>
                </div>

                <!-- SECTION 4: Notes de Frais -->
                <div class="space-y-4 pt-6 border-t border-[#3a2e24]">
                    <h4 class="text-xl font-bold text-white">4. Notes de Frais</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div>
                            <p class="text-sm text-text-secondary mb-4 leading-relaxed">
                                Remboursement des dépenses professionnelles.
                            </p>
                            <h5 class="font-bold text-white mb-2">Processus :</h5>
                            <ul class="space-y-4">
                                <li class="flex gap-3">
                                    <div
                                        class="w-6 h-6 rounded-full bg-surface-highlight flex items-center justify-center text-xs font-bold text-white border border-[#3a2e24]">
                                        1</div>
                                    <div class="text-sm text-text-secondary">Créez une Note de Frais.</div>
                                </li>
                                <li class="flex gap-3">
                                    <div
                                        class="w-6 h-6 rounded-full bg-surface-highlight flex items-center justify-center text-xs font-bold text-white border border-[#3a2e24]">
                                        2</div>
                                    <div class="text-sm text-text-secondary">Ajoutez vos dépenses (Montant en FCFA).
                                    </div>
                                </li>
                                <li class="flex gap-3">
                                    <div
                                        class="w-6 h-6 rounded-full bg-surface-highlight flex items-center justify-center text-xs font-bold text-white border border-[#3a2e24]">
                                        3</div>
                                    <div class="text-sm text-text-secondary">Ajoutez les photos des reçus (Obligatoire).
                                    </div>
                                </li>
                                <li class="flex gap-3">
                                    <div
                                        class="w-6 h-6 rounded-full bg-surface-highlight flex items-center justify-center text-xs font-bold text-white border border-[#3a2e24]">
                                        4</div>
                                    <div class="text-sm text-text-secondary">Soumettez pour remboursement.</div>
                                </li>
                            </ul>
                        </div>
                        <div class="bg-surface-dark border border-[#3a2e24] p-4 rounded-xl">
                            <h5 class="font-bold text-white mb-3 text-sm">Exemples de Plafonds</h5>
                            <div class="space-y-3">
                                <div class="p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                                    <span class="text-red-400 text-xs font-bold block mb-1">Repas</span>
                                    <span class="text-text-secondary text-xs">Plafond indicatif : 15 000 FCFA.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 5: Recrutement -->
                <div class="pt-6 border-t border-[#3a2e24]">
                    <h4 class="text-xl font-bold text-white mb-4">5. Recrutement</h4>
                    <p class="text-sm text-text-secondary mb-3">
                        Gérez vos offres d'emploi et suivez les candidats dans le Pipeline de recrutement.
                    </p>
                </div>
            </div>

            <!-- Inventory -->
            <div x-show="activeSection === 'inventory'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-8" style="display: none;">

                <div class="flex items-center gap-4 border-b border-[#3a2e24] pb-6">
                    <div class="p-3 bg-amber-500/20 rounded-xl">
                        <span class="material-symbols-outlined text-amber-500 text-3xl">inventory_2</span>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white">Stocks & Achats</h3>
                        <p class="text-text-secondary">Maîtrisez vos produits, vos entrepôts et vos approvisionnements.
                        </p>
                    </div>
                </div>

                <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6">
                    <h4 class="font-bold text-white mb-6">Concepts Clés du Stock</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-4 bg-surface-highlight rounded-xl text-center">
                            <span class="block text-2xl font-bold text-white mb-1">Physique</span>
                            <span class="text-xs text-text-secondary">Ce qui est réellement sur vos étagères dans
                                l'entrepôt.</span>
                        </div>
                        <div class="p-4 bg-surface-highlight rounded-xl text-center relative">
                            <span
                                class="absolute top-1/2 -left-3 -translate-y-1/2 material-symbols-outlined text-text-secondary">remove</span>
                            <span class="block text-2xl font-bold text-amber-500 mb-1">Réservé</span>
                            <span class="text-xs text-text-secondary">Produits vendus (commandes confirmées) mais pas
                                encore expédiés.</span>
                        </div>
                        <div class="p-4 bg-surface-highlight rounded-xl text-center relative">
                            <span
                                class="absolute top-1/2 -left-3 -translate-y-1/2 material-symbols-outlined text-text-secondary">drag_handle</span>
                            <span class="block text-2xl font-bold text-green-500 mb-1">Disponible</span>
                            <span class="text-xs text-text-secondary">Quantité réelle que vous pouvez encore vendre
                                aujourd'hui.</span>
                        </div>
                    </div>
                </div>

                <div class="prose prose-invert max-w-none text-text-secondary text-sm">
                    <h5 class="text-white font-bold">Comment réapprovisionner ?</h5>
                    <p>
                        Lorsque le stock disponible passe sous le seuil d'alerte, vous recevez une notification.
                        Allez dans <span class="text-white font-bold">Achats > Commandes Fournisseurs</span> pour créer
                        une commande d'achat.
                        Une fois la marchandise reçue, cliquez sur <span
                            class="text-white font-bold">"Réceptionner"</span> dans la commande pour augmenter
                        automatiquement vos stocks.
                    </p>
                </div>
            </div>

            <!-- Finance -->
            <div x-show="activeSection === 'finance'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-8" style="display: none;">

                <div class="flex items-center gap-4 border-b border-[#3a2e24] pb-6">
                    <div class="p-3 bg-emerald-500/20 rounded-xl">
                        <span class="material-symbols-outlined text-emerald-500 text-3xl">payments</span>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white">Finance & Facturation</h3>
                        <p class="text-text-secondary">Gérez vos factures et suivez vos encaissements.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <x-ui.card>
                        <h4 class="text-lg font-bold text-white mb-4">Facturation</h4>
                        <p class="text-sm text-text-secondary mb-4">
                            La facturation est la dernière étape du cycle de vente. Vous pouvez créer une facture :
                        </p>
                        <ul class="space-y-2 text-sm text-text-secondary list-disc list-inside">
                            <li>Manuellement depuis le menu Finance.</li>
                            <li>En un clic depuis une <strong>Commande Client</strong> livrée.</li>
                            <li>Automatiquement via les <strong>Contrats</strong> récurrents.</li>
                        </ul>
                    </x-ui.card>

                    <x-ui.card>
                        <h4 class="text-lg font-bold text-white mb-4">Paiements</h4>
                        <p class="text-sm text-text-secondary mb-4">
                            Pour enregistrer un paiement reçu :
                        </p>
                        <ol class="space-y-2 text-sm text-text-secondary list-decimal list-inside">
                            <li>Ouvrez la facture concernée.</li>
                            <li>Cliquez sur le bouton "Enregistrer un paiement".</li>
                            <li>Saisissez le montant (FCFA) et le mode (Virement, Chèque...).</li>
                        </ol>
                        <p class="text-xs text-text-secondary mt-4 italic">
                            Si le paiement est partiel, la facture reste "Partiellement Payée" et le solde dû reste
                            visible.
                        </p>
                    </x-ui.card>
                </div>
            </div>

            <!-- Productivity -->
            <div x-show="activeSection === 'productivity'"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-8" style="display: none;">

                <div class="flex items-center gap-4 border-b border-[#3a2e24] pb-6">
                    <div class="p-3 bg-blue-500/20 rounded-xl">
                        <span class="material-symbols-outlined text-blue-500 text-3xl">check_circle</span>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white">Productivité & Projets</h3>
                        <p class="text-text-secondary">Organisez votre travail et mesurez votre rentabilité.</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6">
                        <h4 class="font-bold text-white mb-4">Gestion de Projet</h4>
                        <p class="text-sm text-text-secondary mb-4">
                            Un projet regroupe des tâches, des membres d'équipe et un budget.
                            Utilisez la vue <strong>Kanban</strong> pour visualiser l'avancement des tâches :
                        </p>
                        <div class="flex flex-wrap gap-4 mt-4">
                            <span
                                class="px-3 py-1 rounded bg-surface-highlight border border-[#3a2e24] text-text-secondary text-xs">À
                                Faire</span>
                            <span
                                class="material-symbols-outlined text-text-secondary text-sm pt-1">arrow_forward</span>
                            <span
                                class="px-3 py-1 rounded bg-blue-500/20 border border-blue-500/30 text-blue-400 text-xs">En
                                Cours</span>
                            <span
                                class="material-symbols-outlined text-text-secondary text-sm pt-1">arrow_forward</span>
                            <span
                                class="px-3 py-1 rounded bg-green-500/20 border border-green-500/30 text-green-400 text-xs">Terminé</span>
                        </div>
                    </div>

                    <div
                        class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6 flex flex-col md:flex-row gap-6 items-center">
                        <div class="flex-1">
                            <h4 class="font-bold text-white mb-2">Time Tracker (Suivi du Temps)</h4>
                            <p class="text-sm text-text-secondary leading-relaxed">
                                Pour analyser la rentabilité d'un projet, il est crucial de suivre le temps passé.
                                Utilisez le chronomètre intégré dans chaque tâche, ou saisissez vos heures manuellement
                                en fin de journée dans votre Feuille de Temps.
                            </p>
                        </div>
                        <div class="p-4 bg-surface-highlight rounded-xl border border-[#3a2e24]">
                            <div class="flex items-center gap-3 text-white font-mono text-xl">
                                <span
                                    class="material-symbols-outlined text-red-500 animate-pulse">radio_button_checked</span>
                                01:23:45
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>