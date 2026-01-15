<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>ERP WMC - Solution de Gestion Tout-en-un</title>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .custom-gradient-text {
            background: linear-gradient(135deg, #ffffff 0%, #baab9c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .glass-panel {
            background: rgba(38, 30, 23, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>

<body
    class="font-display bg-background-dark text-text-light antialiased overflow-x-hidden selection:bg-primary/30 selection:text-white">
    <nav class="fixed top-0 left-0 right-0 z-50 border-b border-white/5 bg-background-dark/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="flex items-center justify-center rounded-xl bg-gradient-to-br from-primary to-orange-600 p-2 shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-white text-2xl">grid_view</span>
                </div>
                <span class="text-xl font-bold text-white tracking-tight">ERP WMC</span>
            </div>
            <div class="hidden md:flex items-center gap-8">
                <a class="text-sm font-medium text-text-secondary hover:text-primary transition-colors"
                    href="#fonctionnalites">Fonctionnalités</a>
                
                <a class="text-sm font-medium text-text-secondary hover:text-primary transition-colors"
                    href="#temoignages">Témoignages</a>
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <a class="hidden sm:block text-sm font-bold text-white hover:text-primary transition-colors"
                        href="{{ url('/dashboard') }}">Dashboard</a>
                @else


                    

                        <a class="bg-primary hover:bg-primary-hover text-white text-sm font-bold py-2.5 px-5 rounded-lg transition-all shadow-lg shadow-primary/25 hover:shadow-primary/40 transform hover:-translate-y-0.5"
                            href="{{ route('login') }}">
                            Connexion
                        </a>
                    
                @endauth
            </div>
        </div>
    </nav>

    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-hero-pattern pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface-card border border-white/10 mb-6">
                    <span class="flex h-2 w-2 rounded-full bg-primary"></span>
                    <span class="text-xs font-bold text-primary uppercase tracking-wider">Nouveau : Module IA
                        Intégré</span>
                </div>
                <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight mb-6 text-white leading-[1.1]">
                    Gérez votre entreprise <br />
                    <span class="text-primary">sans limites.</span>
                </h1>
                <p class="text-lg text-text-secondary mb-10 leading-relaxed max-w-2xl mx-auto">
                    La plateforme unique pour unifier CRM, RH, Finance et Logistique.
                    Optimisez vos processus et prenez des décisions éclairées grâce à ERP WMC.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('login') }}"
                        class="w-full sm:w-auto bg-white text-background-dark hover:bg-gray-100 text-base font-bold py-4 px-8 rounded-xl transition-colors text-center">
                        Démarrer maintenant
                    </a>
                    <button
                        class="w-full sm:w-auto bg-surface-card border border-white/10 hover:border-primary/50 text-white text-base font-bold py-4 px-8 rounded-xl transition-all flex items-center justify-center gap-2 group">
                        <span>Voir la vidéo</span>
                        <span
                            class="material-symbols-outlined group-hover:translate-x-1 transition-transform">play_circle</span>
                    </button>
                </div>
            </div>

            <!-- Dashboard Preview -->
            <div class="relative mt-12 mx-auto max-w-5xl">
                <div class="absolute -inset-1 bg-gradient-to-r from-primary to-orange-900 rounded-2xl blur opacity-20">
                </div>
                <div class="relative bg-surface-dark border border-white/10 rounded-2xl shadow-2xl overflow-hidden">
                    <div class="flex items-center gap-2 px-4 py-3 border-b border-white/5 bg-surface-card">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500/20 border border-red-500/50"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500/20 border border-yellow-500/50"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500/20 border border-green-500/50"></div>
                        </div>
                        <div class="mx-auto bg-black/20 px-3 py-1 rounded-md text-[10px] text-text-secondary font-mono">
                            erp-wmc/dashboard</div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6 lg:p-8 bg-background-dark">
                        <div class="col-span-1 space-y-4">
                            <div class="bg-surface-card p-5 rounded-xl border border-white/5">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                        <span class="material-symbols-outlined">trending_up</span>
                                    </div>
                                    <span
                                        class="text-xs font-bold text-green-400 bg-green-400/10 px-2 py-1 rounded">+24%</span>
                                </div>
                                <p class="text-text-secondary text-sm">Revenus (Mensuel)</p>
                                <p class="text-2xl font-bold text-white mt-1">29 500 000 FCFA</p>
                            </div>
                            <div class="bg-surface-card p-4 rounded-xl border border-white/5 space-y-2">
                                <div
                                    class="flex items-center gap-3 p-2 bg-primary/10 rounded-lg text-primary border border-primary/20">
                                    <span class="material-symbols-outlined text-sm">dashboard</span>
                                    <span class="text-sm font-bold">Vue d'ensemble</span>
                                </div>
                                <div class="flex items-center gap-3 p-2 text-text-secondary">
                                    <span class="material-symbols-outlined text-sm">group</span>
                                    <span class="text-sm">CRM &amp; Clients</span>
                                </div>
                                <div class="flex items-center gap-3 p-2 text-text-secondary">
                                    <span class="material-symbols-outlined text-sm">inventory_2</span>
                                    <span class="text-sm">Stocks</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-1 md:col-span-2 space-y-4">
                            <div
                                class="bg-surface-card p-6 rounded-xl border border-white/5 h-48 relative overflow-hidden flex flex-col">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-sm font-bold text-white">Performance Globale</h3>
                                    <span class="text-xs text-text-secondary">Derniers 30 jours</span>
                                </div>
                                <div class="flex-1 flex items-end gap-2 px-2">
                                    @foreach([40, 60, 30, 80, 55, 75] as $height)
                                        <div class="w-full bg-primary/20 rounded-t-sm h-[{{ $height }}%] relative group">
                                            <div
                                                class="absolute inset-x-0 bottom-0 bg-primary h-[0%] group-hover:h-full transition-all duration-700">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="bg-surface-card p-0 rounded-xl border border-white/5 overflow-hidden">
                                <div class="px-5 py-3 border-b border-white/5 flex justify-between">
                                    <span class="text-xs font-bold text-white uppercase">Dernières Activités</span>
                                </div>
                                <div class="p-4 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="h-8 w-8 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400 text-xs font-bold">
                                                JD</div>
                                            <div>
                                                <p class="text-sm text-white font-medium">Jean Dupont</p>
                                                <p class="text-xs text-text-secondary">Nouvelle commande #2049</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-white font-bold">785 000 FCFA</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="h-8 w-8 rounded-full bg-purple-500/20 flex items-center justify-center text-purple-400 text-xs font-bold">
                                                MA</div>
                                            <div>
                                                <p class="text-sm text-white font-medium">Marie Alibert</p>
                                                <p class="text-xs text-text-secondary">Ticket support résolu</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-green-400">Terminé</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-background-dark via-transparent to-transparent opacity-60 pointer-events-none">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Section -->
    <section class="py-10 border-y border-white/5 bg-surface-card/30">
        <div class="max-w-7xl mx-auto px-6">
            <p class="text-center text-sm font-medium text-text-secondary mb-8 uppercase tracking-widest">Plus de 500
                entreprises nous font confiance</p>
            <div
                class="flex flex-wrap justify-center items-center gap-12 md:gap-20 opacity-60 grayscale hover:grayscale-0 transition-all duration-500">
                @foreach(['Acme Corp', 'Infinity', 'GlobalSys', 'Prestige', 'StartUp Inc'] as $company)
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-3xl">token</span>
                        <span class="text-xl font-bold font-display">{{ $company }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-24 bg-background-dark" id="fonctionnalites">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">Tout ce dont vous avez besoin. <br /><span
                        class="text-text-secondary">Rien de superflu.</span></h2>
                <p class="text-lg text-text-secondary max-w-2xl mx-auto">
                    Une suite complète de modules interconnectés pour gérer chaque aspect de votre activité depuis une
                    interface unique.
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Feature Cards -->
                <div
                    class="group p-8 rounded-2xl bg-surface-card border border-white/5 hover:border-primary/50 hover:bg-surface-card/80 transition-all duration-300">
                    <div
                        class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-primary text-3xl">groups</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">CRM &amp; Ventes</h3>
                    <p class="text-text-secondary leading-relaxed">Suivez vos prospects du premier contact à la
                        signature. Gestion de pipeline visuelle.</p>
                </div>
                <div
                    class="group p-8 rounded-2xl bg-surface-card border border-white/5 hover:border-primary/50 hover:bg-surface-card/80 transition-all duration-300">
                    <div
                        class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-primary text-3xl">payments</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Finance</h3>
                    <p class="text-text-secondary leading-relaxed">Devis, factures et suivi de trésorerie en temps réel.
                    </p>
                </div>
                <div
                    class="group p-8 rounded-2xl bg-surface-card border border-white/5 hover:border-primary/50 hover:bg-surface-card/80 transition-all duration-300">
                    <div
                        class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-primary text-3xl">inventory_2</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Stocks</h3>
                    <p class="text-text-secondary leading-relaxed">Contrôle précis de vos inventaires multi-entrepôts.
                    </p>
                </div>
                <div
                    class="group p-8 rounded-2xl bg-surface-card border border-white/5 hover:border-primary/50 hover:bg-surface-card/80 transition-all duration-300">
                    <div
                        class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-primary text-3xl">badge</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">RH</h3>
                    <p class="text-text-secondary leading-relaxed">Dossiers employés, gestion des congés et notes de
                        frais.</p>
                </div>
                <div
                    class="group p-8 rounded-2xl bg-surface-card border border-white/5 hover:border-primary/50 hover:bg-surface-card/80 transition-all duration-300">
                    <div
                        class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-primary text-3xl">speed</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Productivité</h3>
                    <p class="text-text-secondary leading-relaxed">Gestion de projet et suivi des temps.</p>
                </div>
                <div
                    class="group p-8 rounded-2xl bg-surface-card border border-white/5 hover:border-primary/50 hover:bg-surface-card/80 transition-all duration-300">
                    <div
                        class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-primary text-3xl">calendar_month</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Agenda</h3>
                    <p class="text-text-secondary leading-relaxed">Planification des ressources et rendez-vous.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Analytics Section -->
    <section class="py-24 border-t border-white/5 bg-surface-card/20">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center gap-16">
                <div class="lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden border border-white/10 shadow-2xl group">
                        <div
                            class="aspect-video bg-gradient-to-br from-surface-card to-black flex items-center justify-center relative">
                            <span class="material-symbols-outlined text-9xl text-white/5">analytics</span>
                            <div
                                class="absolute top-10 left-10 bg-surface-dark p-4 rounded-xl border border-white/10 shadow-xl animate-[bounce_3s_infinite]">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-2 h-2 rounded-full bg-primary"></div>
                                    <span class="text-xs font-bold text-white">Conversion</span>
                                </div>
                                <div class="text-2xl font-bold text-white">12.5%</div>
                            </div>
                            <div
                                class="absolute bottom-10 right-10 bg-surface-dark p-4 rounded-xl border border-white/10 shadow-xl">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                    <span class="text-xs font-bold text-white">Objectif</span>
                                </div>
                                <div class="text-2xl font-bold text-white">Atteint</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">Analytique puissante pour des décisions
                        rapides</h2>
                    <p class="text-text-secondary text-lg mb-8 leading-relaxed">
                        Ne naviguez plus à l'aveugle. ERP WMC consolide vos données en tableaux de bord clairs et
                        actionnables.
                    </p>
                    <ul class="space-y-4">
                        @foreach(['Rapports personnalisables', 'Export comptable', 'Données sécurisées'] as $item)
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary mt-1">check_circle</span>
                                <div>
                                    <h4 class="font-bold text-white">{{ $item }}</h4>
                                    <p class="text-sm text-text-secondary">Compatible avec tous les logiciels majeurs.</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-24 bg-background-dark" id="temoignages">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-center text-white mb-16">Ils ont transformé leur activité</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="p-8 rounded-2xl bg-surface-card border border-white/5 relative">
                    <span
                        class="material-symbols-outlined text-6xl text-primary/10 absolute top-4 right-4">format_quote</span>
                    <p class="text-text-secondary mb-6 relative z-10 italic">"Avant ERP WMC, nous utilisions 4 logiciels
                        différents. Aujourd'hui, tout est centralisé. Nous avons gagné 20% de productivité."</p>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center text-white font-bold">
                            TB</div>
                        <div>
                            <h4 class="font-bold text-white">Thomas Bernard</h4>
                            <p class="text-xs text-text-secondary">CEO, TechStart</p>
                        </div>
                    </div>
                </div>
                <!-- Testimonial 2 -->
                <div class="p-8 rounded-2xl bg-surface-card border border-white/5 relative">
                    <span
                        class="material-symbols-outlined text-6xl text-primary/10 absolute top-4 right-4">format_quote</span>
                    <p class="text-text-secondary mb-6 relative z-10 italic">"La gestion des stocks est enfin simple.
                        Plus de ruptures imprévues, et le lien avec la facturation est automatique."</p>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center text-white font-bold">
                            SC</div>
                        <div>
                            <h4 class="font-bold text-white">Sarah Connor</h4>
                            <p class="text-xs text-text-secondary">Dir. Logistique</p>
                        </div>
                    </div>
                </div>
                <!-- Testimonial 3 -->
                <div class="p-8 rounded-2xl bg-surface-card border border-white/5 relative">
                    <span
                        class="material-symbols-outlined text-6xl text-primary/10 absolute top-4 right-4">format_quote</span>
                    <p class="text-text-secondary mb-6 relative z-10 italic">"Le support client est incroyable. La mise
                        en place a été rapide et l'équipe s'est adaptée à nos processus."</p>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center text-white font-bold">
                            KR</div>
                        <div>
                            <h4 class="font-bold text-white">Kyle Reese</h4>
                            <p class="text-xs text-text-secondary">Fondateur</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20 px-6">
        <div
            class="max-w-5xl mx-auto rounded-3xl bg-gradient-to-r from-primary to-orange-700 p-12 md:p-20 text-center relative overflow-hidden">
            <div
                class="absolute top-0 left-0 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2">
            </div>
            <div
                class="absolute bottom-0 right-0 w-64 h-64 bg-black opacity-20 rounded-full blur-3xl translate-x-1/2 translate-y-1/2">
            </div>
            <div class="relative z-10">
                <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">Prêt à passer à la vitesse supérieure ?</h2>
                <p class="text-orange-100 text-lg mb-10 max-w-2xl mx-auto">Rejoignez les entreprises modernes qui ont
                    choisi la simplicité et l'efficacité.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ route('login') }}"
                        class="bg-white text-primary font-bold py-4 px-8 rounded-xl shadow-xl hover:shadow-2xl hover:bg-gray-50 transition-all transform hover:-translate-y-1">
                        Commencer gratuitement
                    </a>
                    <a href="#"
                        class="bg-black/20 text-white border border-white/20 font-bold py-4 px-8 rounded-xl hover:bg-black/30 transition-all">
                        Parler à un expert
                    </a>
                </div>
                <p class="mt-6 text-sm text-orange-100 opacity-80">Pas de carte de crédit requise • Annulation à tout
                    moment</p>
            </div>
        </div>
    </section>

    <footer class="bg-surface-dark border-t border-white/5 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8 mb-12">
                <div class="col-span-2 lg:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-primary text-2xl">grid_view</span>
                        <span class="text-xl font-bold text-white">ERP WMC</span>
                    </div>
                    <p class="text-text-secondary text-sm mb-6 max-w-xs">
                        La solution tout-en-un pour les entreprises ambitieuses. Simplifiez, automatisez, grandissez.
                    </p>
                </div>
                <!-- Footer links here (simplified for brevity, you can add more cols) -->
                <div class="col-span-1">
                    <h4 class="text-white font-bold mb-4">Produit</h4>
                    <ul class="space-y-2 text-sm text-text-secondary">
                        <li><a class="hover:text-primary transition-colors" href="#">Fonctionnalités</a></li>
                        <li><a class="hover:text-primary transition-colors" href="#">Tarifs</a></li>
                    </ul>
                </div>
                <div class="col-span-1">
                    <h4 class="text-white font-bold mb-4">Légal</h4>
                    <ul class="space-y-2 text-sm text-text-secondary">
                        <li><a class="hover:text-primary transition-colors" href="#">Confidentialité</a></li>
                        <li><a class="hover:text-primary transition-colors" href="#">Mentions Légales</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/5 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-text-secondary text-sm">© {{ date('Y') }} ERP WMC. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

</body>

</html>