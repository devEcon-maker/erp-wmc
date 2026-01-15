<x-app-layout>
    <!-- Page Heading -->
    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">Vue d'ensemble</h2>
            <p class="text-text-secondary mt-1">Bienvenue {{ Auth::user()->name }}, voici ce qu'il se passe aujourd'hui
                chez ERP WMC.</p>
        </div>
        <div class="flex gap-2">
            <span
                class="text-sm font-medium text-text-secondary bg-surface-dark px-3 py-1 rounded-lg border border-[#3a2e24]">Dernière
                MAJ: {{ date('H:i') }}</span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Stat 1 -->
        <div
            class="bg-surface-dark p-5 rounded-2xl border border-[#3a2e24] hover:border-primary/30 transition-colors group">
            <div class="flex justify-between items-start mb-4">
                <div
                    class="p-2 bg-primary/10 rounded-lg text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined">trending_up</span>
                </div>
                <span class="text-xs font-bold text-[#0bda16] bg-[#0bda16]/10 px-2 py-1 rounded">+12%</span>
            </div>
            <p class="text-text-secondary text-sm font-medium">Revenus (Mensuel)</p>
            <p class="text-2xl font-bold text-white mt-1">45 230 FCFA</p>
        </div>
        <!-- Stat 2 -->
        <div
            class="bg-surface-dark p-5 rounded-2xl border border-[#3a2e24] hover:border-primary/30 transition-colors group">
            <div class="flex justify-between items-start mb-4">
                <div
                    class="p-2 bg-primary/10 rounded-lg text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined">lightbulb</span>
                </div>
            </div>
            <p class="text-text-secondary text-sm font-medium">Opportunités actives</p>
            <p class="text-2xl font-bold text-white mt-1">12 Dossiers</p>
        </div>
        <!-- Stat 3 -->
        <div
            class="bg-surface-dark p-5 rounded-2xl border border-[#3a2e24] hover:border-primary/30 transition-colors group">
            <div class="flex justify-between items-start mb-4">
                <div
                    class="p-2 bg-primary/10 rounded-lg text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined">group</span>
                </div>
                <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-1 rounded">2 Absents</span>
            </div>
            <p class="text-text-secondary text-sm font-medium">Effectif Présent</p>
            <p class="text-2xl font-bold text-white mt-1">24 / 26</p>
        </div>
        <!-- Stat 4 -->
        <div
            class="bg-surface-dark p-5 rounded-2xl border border-[#3a2e24] hover:border-primary/30 transition-colors group">
            <div class="flex justify-between items-start mb-4">
                <div
                    class="p-2 bg-primary/10 rounded-lg text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined">warning</span>
                </div>
                <span class="text-xs font-bold text-red-400 bg-red-400/10 px-2 py-1 rounded">Action requise</span>
            </div>
            <p class="text-text-secondary text-sm font-medium">Alertes Stocks</p>
            <p class="text-2xl font-bold text-white mt-1">5 Articles</p>
        </div>
    </div>

    <!-- Main Dashboard Sections -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Chart Section (Takes 2 cols) -->
        <div class="xl:col-span-2 bg-surface-dark rounded-2xl border border-[#3a2e24] p-6 flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-lg font-bold text-white">Flux Financier</h3>
                    <p class="text-sm text-text-secondary">Recettes vs Dépenses sur 6 mois</p>
                </div>
                <button class="text-sm font-medium text-primary hover:text-white transition-colors">Voir Rapport
                    Complet</button>
            </div>
            <div class="flex-1 w-full min-h-[250px] relative">
                <!-- Reusing the SVG structure but tailored to the theme -->
                <svg class="w-full h-full" preserveaspectratio="none" viewbox="0 0 800 250">
                    <defs>
                        <lineargradient id="chartGradient" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="#f48c25" stop-opacity="0.3"></stop>
                            <stop offset="100%" stop-color="#f48c25" stop-opacity="0"></stop>
                        </lineargradient>
                    </defs>
                    <!-- Grid lines -->
                    <line stroke="#3a2e24" stroke-width="1" x1="0" x2="800" y1="200" y2="200"></line>
                    <line stroke="#3a2e24" stroke-dasharray="4 4" stroke-width="1" x1="0" x2="800" y1="150" y2="150">
                    </line>
                    <line stroke="#3a2e24" stroke-dasharray="4 4" stroke-width="1" x1="0" x2="800" y1="100" y2="100">
                    </line>
                    <line stroke="#3a2e24" stroke-dasharray="4 4" stroke-width="1" x1="0" x2="800" y1="50" y2="50">
                    </line>
                    <!-- The Chart Line -->
                    <path
                        d="M0,200 C100,200 100,120 200,130 C300,140 300,60 400,80 C500,100 500,150 600,120 C700,90 700,40 800,20"
                        fill="none" stroke="#f48c25" stroke-linecap="round" stroke-linejoin="round" stroke-width="4">
                    </path>
                    <!-- The Fill Area -->
                    <path
                        d="M0,200 C100,200 100,120 200,130 C300,140 300,60 400,80 C500,100 500,150 600,120 C700,90 700,40 800,20 V250 H0 Z"
                        fill="url(#chartGradient)" opacity="0.6"></path>
                </svg>
            </div>
            <div class="flex justify-between px-2 mt-4 text-xs font-bold text-text-secondary uppercase tracking-wider">
                <span>Jan</span>
                <span>Fév</span>
                <span>Mar</span>
                <span>Avr</span>
                <span>Mai</span>
                <span>Juin</span>
            </div>
        </div>

        <!-- Agenda / Tasks Widget -->
        <div class="bg-surface-dark rounded-2xl border border-[#3a2e24] p-6 flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-white">Agenda du Jour</h3>
                <button class="p-1 text-text-secondary hover:text-white rounded-md">
                    <span class="material-symbols-outlined">more_horiz</span>
                </button>
            </div>
            <div
                class="space-y-6 relative before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-[2px] before:bg-[#3a2e24]">
                <!-- Event 1 -->
                <div class="relative pl-10">
                    <div
                        class="absolute left-[13px] top-1 w-3.5 h-3.5 rounded-full border-2 border-primary bg-background-dark z-10">
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-bold text-primary">09:30 - 10:30</span>
                        <p class="text-sm font-bold text-white">Réunion Stratégique</p>
                        <p class="text-xs text-text-secondary">Salle de conférence A • Avec Direction</p>
                    </div>
                </div>
                <!-- Event 2 -->
                <div class="relative pl-10">
                    <div
                        class="absolute left-[13px] top-1 w-3.5 h-3.5 rounded-full border-2 border-[#baab9c] bg-background-dark z-10">
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-bold text-text-secondary">11:00 - 12:00</span>
                        <p class="text-sm font-bold text-white">Point Client: TechSol</p>
                        <p class="text-xs text-text-secondary">Google Meet</p>
                    </div>
                </div>
                <!-- Event 3 -->
                <div class="relative pl-10">
                    <div
                        class="absolute left-[13px] top-1 w-3.5 h-3.5 rounded-full border-2 border-[#baab9c] bg-background-dark z-10">
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-bold text-text-secondary">14:00 - 15:30</span>
                        <p class="text-sm font-bold text-white">Revue Stocks Q2</p>
                        <p class="text-xs text-text-secondary">Entrepôt B</p>
                    </div>
                </div>
            </div>
            <button
                class="mt-auto w-full py-2.5 rounded-lg border border-primary/30 text-primary text-sm font-bold hover:bg-primary/10 transition-colors">
                Voir tout l'agenda
            </button>
        </div>

        <!-- CRM Latest Leads (Takes 2 cols on wide, 1 on xl) -->
        <div
            class="xl:col-span-2 bg-surface-dark rounded-2xl border border-[#3a2e24] p-0 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-[#3a2e24] flex justify-between items-center">
                <h3 class="text-lg font-bold text-white">Derniers Prospects CRM</h3>
                <a class="text-sm text-primary font-medium hover:underline" href="#">Voir Pipeline</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-xs text-text-secondary uppercase bg-[#251d16]">
                            <th class="px-6 py-4 font-bold">Contact</th>
                            <th class="px-6 py-4 font-bold">Entreprise</th>
                            <th class="px-6 py-4 font-bold">Montant Est.</th>
                            <th class="px-6 py-4 font-bold">Statut</th>
                            <th class="px-6 py-4 font-bold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-[#3a2e24]">
                        <tr class="group hover:bg-surface-highlight transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-full bg-cover bg-center"
                                        data-alt="Portrait of Sarah Connor"
                                        style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDkJmrVBwlZ_UPRyw6pSQsXo6WLOgbF0Q63tKUXz_HrptWeiaPF02yRcPIiKTI9xoSiovRSZr6toKcSe37XVio8rxDi-FFo4KrLVDW7afJPfu6Vyn_oVTciWsD8mOMJZ75scBU5XNGzHjD0t8NuxuNs7AnV6bxVx0O4uTl9alo4xQeZ0d65e2u_vwWBPb-GWYYarCsYt--O-LGBtPBZ-k1RaATbidhkm84JV-hNuXsjdFwGzGZVhi2cuIQCgEbvOt5cQJ7k5Vwm_MIz");'>
                                    </div>
                                    <span class="font-bold text-white">Sarah Connor</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-text-secondary">Cyberdyne Systems</td>
                            <td class="px-6 py-4 text-white font-medium">12 500 FCFA</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-400">
                                    Négociation
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-text-secondary hover:text-white"><span
                                        class="material-symbols-outlined text-lg">more_vert</span></button>
                            </td>
                        </tr>
                        <tr class="group hover:bg-surface-highlight transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="size-8 rounded-full bg-purple-900 flex items-center justify-center text-white font-bold text-xs">
                                        JM</div>
                                    <span class="font-bold text-white">John Matrix</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-text-secondary">Commando Inc.</td>
                            <td class="px-6 py-4 text-white font-medium">4 200 FCFA</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-500/10 text-green-400">
                                    Signé
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-text-secondary hover:text-white"><span
                                        class="material-symbols-outlined text-lg">more_vert</span></button>
                            </td>
                        </tr>
                        <tr class="group hover:bg-surface-highlight transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-full bg-cover bg-center"
                                        data-alt="Portrait of Kyle Reese"
                                        style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDnj176P99lffkQXS0fIXwVqp4evcMWTH00f2lMGn5qKKHQ1BrYUMTLQ7rZzXF2qv5CARRWumMfYAau3jFMbhfHDityKLyfmoCteHHEyFeMahWX_bRdqgKgzMyBGIYkK86gjTK_cQstBUkdtX_YZ3uQJd7pWSNJipXZC7CuhmsXgSutv_UhdH2TqK_rHPJlscXrnPs3yi4EO6jpdkDs5lJTd1U8rneXvCCGovgpbEiq_xk7oKbv24AOULL57YGLcZ_Ed32qTM2v-nuZ");'>
                                    </div>
                                    <span class="font-bold text-white">Kyle Reese</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-text-secondary">Resistance Ltd.</td>
                            <td class="px-6 py-4 text-white font-medium">8 900 FCFA</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-500/10 text-yellow-400">
                                    En attente
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-text-secondary hover:text-white"><span
                                        class="material-symbols-outlined text-lg">more_vert</span></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Stock Alerts (Small Widget) -->
        <div class="bg-surface-dark rounded-2xl border border-[#3a2e24] p-6 flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-white">Alertes Stock</h3>
                <span class="material-symbols-outlined text-primary">inventory</span>
            </div>
            <div class="flex flex-col gap-4">
                <div
                    class="flex items-center justify-between p-3 rounded-xl bg-background-dark border border-[#3a2e24]">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-lg bg-[#3a2e24] flex items-center justify-center">
                            <span class="material-symbols-outlined text-text-secondary">print</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white">Papier A4</p>
                            <p class="text-xs text-red-400">Stock: 5 ramettes</p>
                        </div>
                    </div>
                    <button
                        class="text-xs font-bold bg-primary text-white px-3 py-1.5 rounded-lg hover:bg-primary/90">Commander</button>
                </div>
                <div
                    class="flex items-center justify-between p-3 rounded-xl bg-background-dark border border-[#3a2e24]">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-lg bg-[#3a2e24] flex items-center justify-center">
                            <span class="material-symbols-outlined text-text-secondary">ink_highlighter</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white">Cartouches N</p>
                            <p class="text-xs text-orange-400">Stock: 2 unités</p>
                        </div>
                    </div>
                    <button
                        class="text-xs font-bold bg-primary text-white px-3 py-1.5 rounded-lg hover:bg-primary/90">Commander</button>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-[#3a2e24]">
                <p class="text-sm text-text-secondary mb-2 font-medium">Capacité Entrepôt</p>
                <div class="w-full bg-[#3a2e24] rounded-full h-2.5">
                    <div class="bg-gradient-to-r from-primary to-orange-300 h-2.5 rounded-full" style="width: 70%">
                    </div>
                </div>
                <div class="flex justify-between mt-1 text-xs text-text-secondary">
                    <span>Utilisé: 70%</span>
                    <span>Libre: 30%</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>