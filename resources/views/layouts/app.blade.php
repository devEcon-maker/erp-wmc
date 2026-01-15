<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ERP WMC') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    @stack('styles')
</head>

<body
    class="font-display bg-background-light dark:bg-background-dark text-[#111418] dark:text-white antialiased overflow-hidden">
    {{-- Toast Notifications --}}
    <x-ui.toast-notifications />

    <div class="flex h-screen w-full overflow-hidden" x-data="{ sidebarOpen: false }">
        <!-- Side Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="flex w-72 flex-col border-r border-[#3a2e24] bg-background-dark fixed inset-y-0 left-0 z-50 transition-transform duration-300 lg:static lg:translate-x-0 lg:flex">

            <!-- Fixed Header - Branding -->
            <div class="flex-shrink-0 p-4 border-b border-[#3a2e24]">
                <div class="flex items-center gap-3 px-2">
                    <div class="flex items-center justify-center rounded-xl bg-primary/20 p-2">
                        <span class="material-symbols-outlined text-primary text-3xl">grid_view</span>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-white text-lg font-bold leading-tight">ERP WMC</h1>
                        <p class="text-text-secondary text-xs font-medium">v1.0.0 Enterprise</p>
                    </div>
                </div>
            </div>

            <!-- Scrollable Navigation -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-6 sidebar-scrollbar">

                <!-- Dashboard -->
                <div class="flex flex-col gap-1">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                        <span class="material-symbols-outlined">dashboard</span>
                        <p class="text-sm font-bold">Tableau de Bord</p>
                    </a>
                    <a href="{{ route('productivity.dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('productivity.dashboard') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                        <p class="text-sm font-medium">Vue d'ensemble</p>
                    </a>
                </div>

                <!-- CRM Module -->
                @canany(['contacts.view', 'opportunities.view', 'orders.view', 'contracts.view'])
                    <div class="flex flex-col gap-1">
                        <p class="px-3 text-xs font-bold text-text-secondary uppercase tracking-wider mb-1">CRM & Ventes</p>
                        @can('contacts.view')
                            <a href="{{ route('crm.contacts.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('crm.contacts.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">perm_contact_calendar</span>
                                <p class="text-sm font-medium">Contacts</p>
                            </a>
                        @endcan
                        @can('opportunities.view')
                            <a href="{{ route('crm.opportunities.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('crm.opportunities.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">leaderboard</span>
                                <p class="text-sm font-medium">Opportunites</p>
                            </a>
                            <a href="{{ route('crm.proposals.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('crm.proposals.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">description</span>
                                <p class="text-sm font-medium">Devis et Proforma</p>
                            </a>
                        @endcan
                        @can('orders.view')
                            <a href="{{ route('crm.orders.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('crm.orders.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
                                <p class="text-sm font-medium">Commandes</p>
                            </a>
                        @endcan
                        @can('contracts.view')
                            <a href="{{ route('crm.contracts.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('crm.contracts.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">folder_shared</span>
                                <p class="text-sm font-medium">Contrats Physiques</p>
                            </a>
                            <a href="{{ route('crm.reminders.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('crm.reminders.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">notifications_active</span>
                                <p class="text-sm font-medium">Relances</p>
                            </a>
                        @endcan
                    </div>
                @endcanany

                <!-- Finance Module -->
                @canany(['invoices.view', 'payments.view'])
                    <div class="flex flex-col gap-1">
                        <p class="px-3 text-xs font-bold text-text-secondary uppercase tracking-wider mb-1">Finance</p>
                        @can('invoices.view')
                            <a href="{{ route('finance.invoices.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('finance.invoices.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">payments</span>
                                <p class="text-sm font-medium">Factures</p>
                            </a>
                            <a href="{{ route('finance.subscriptions.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('finance.subscriptions.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">repeat</span>
                                <p class="text-sm font-medium">Abonnements</p>
                            </a>
                        @endcan
                    </div>
                @endcanany

                <!-- HR Module -->
                @canany(['employees.view', 'leaves.view', 'leaves.create', 'expenses.view', 'expenses.create', 'recruitment.view'])
                    <div class="flex flex-col gap-1">
                        <p class="px-3 text-xs font-bold text-text-secondary uppercase tracking-wider mb-1">Ressources
                            Humaines</p>
                        @can('employees.view')
                            <a href="{{ route('hr.dashboard') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('hr.dashboard') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">dashboard</span>
                                <p class="text-sm font-medium">Dashboard RH</p>
                            </a>
                        @endcan
                        <!-- Mon espace - accessible a tous les employes -->
                        <a href="{{ route('hr.my-space') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('hr.my-space') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                            <p class="text-sm font-medium">Mon espace</p>
                        </a>
                        @can('employees.view')
                            <a href="{{ route('hr.employees.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('hr.employees.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">badge</span>
                                <p class="text-sm font-medium">Employes</p>
                            </a>
                            <a href="{{ route('hr.org-chart') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('hr.org-chart') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">account_tree</span>
                                <p class="text-sm font-medium">Organigramme</p>
                            </a>
                            <a href="{{ route('hr.attendance.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('hr.attendance.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">fingerprint</span>
                                <p class="text-sm font-medium">Pointage</p>
                            </a>
                        @endcan
                        @canany(['leaves.view', 'leaves.create'])
                            <a href="{{ route('hr.leaves.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('hr.leaves.index') || request()->routeIs('hr.leaves.requests*') || request()->routeIs('hr.leaves.balances') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">event_busy</span>
                                <p class="text-sm font-medium">Conges</p>
                            </a>
                        @endcanany
                        @can('leaves.approve')
                            <a href="{{ route('hr.leaves.approval') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('hr.leaves.approval') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">fact_check</span>
                                <p class="text-sm font-medium">Approbation conges</p>
                            </a>
                        @endcan
                        @can('employees.view')
                            <a href="{{ route('hr.payroll.periods.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('hr.payroll.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">payments</span>
                                <p class="text-sm font-medium">Paie</p>
                            </a>
                            <a href="{{ route('hr.evaluations.periods.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('hr.evaluations.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">star_rate</span>
                                <p class="text-sm font-medium">Evaluations</p>
                            </a>
                        @endcan
                        @canany(['expenses.view', 'expenses.create'])
                            <a href="{{ route('hr.expenses.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('hr.expenses.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                                <p class="text-sm font-medium">Notes de frais</p>
                            </a>
                        @endcanany
                        @canany(['time.view', 'time.create'])
                            <a href="{{ route('hr.timesheets.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('hr.timesheets.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">schedule</span>
                                <p class="text-sm font-medium">Feuilles de temps</p>
                            </a>
                        @endcanany
                        @can('recruitment.view')
                            <a href="{{ route('hr.recruitment.positions.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('hr.recruitment.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">work</span>
                                <p class="text-sm font-medium">Recrutement</p>
                            </a>
                        @endcan
                    </div>
                @endcanany

                <!-- Inventory Module -->
                @canany(['products.view', 'stock.view', 'purchases.view'])
                    <div class="flex flex-col gap-1">
                        <p class="px-3 text-xs font-bold text-text-secondary uppercase tracking-wider mb-1">Stocks &
                            Logistique</p>
                        @can('products.view')
                            <a href="{{ route('inventory.products.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('inventory.products.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">category</span>
                                <p class="text-sm font-medium">Produits</p>
                            </a>
                        @endcan
                        @can('stock.view')
                            <a href="{{ route('inventory.stock.dashboard') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('inventory.stock.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                                <p class="text-sm font-medium">Stocks</p>
                            </a>
                        @endcan
                        @can('purchases.view')
                            <a href="{{ route('inventory.purchases.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('inventory.purchases.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">shopping_bag</span>
                                <p class="text-sm font-medium">Achats</p>
                            </a>
                        @endcan
                    </div>
                @endcanany

                <!-- Productivity & Agenda Module -->
                @canany(['projects.view', 'tasks.view', 'time.view', 'events.view'])
                    <div class="flex flex-col gap-1">
                        <p class="px-3 text-xs font-bold text-text-secondary uppercase tracking-wider mb-1">Productivite</p>
                        @can('projects.view')
                            <a href="{{ route('productivity.projects.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('productivity.projects.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">folder</span>
                                <p class="text-sm font-medium">Projets</p>
                            </a>
                        @endcan
                        @canany(['time.view', 'time.create'])
                            <a href="{{ route('productivity.time-tracker') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('productivity.time-tracker') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">timer</span>
                                <p class="text-sm font-medium">Time Tracker</p>
                            </a>
                        @endcanany
                        @can('events.view')
                            <a href="{{ route('agenda.calendar') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('agenda.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                                <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                                <p class="text-sm font-medium">Agenda</p>
                            </a>
                        @endcan
                    </div>
                @endcanany

                <!-- Administration Module -->
                @can('settings.manage')
                    <div class="flex flex-col gap-1">
                        <p class="px-3 text-xs font-bold text-text-secondary uppercase tracking-wider mb-1">Administration
                        </p>
                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.users.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                            <span class="material-symbols-outlined text-[20px]">group</span>
                            <p class="text-sm font-medium">Utilisateurs</p>
                        </a>
                        <a href="{{ route('admin.roles.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.roles.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                            <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
                            <p class="text-sm font-medium">Roles & Permissions</p>
                        </a>
                        <a href="{{ route('admin.settings.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.settings.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                            <span class="material-symbols-outlined text-[20px]">mail</span>
                            <p class="text-sm font-medium">Configuration SMTP</p>
                        </a>
                    </div>
                @endcan

                <!-- Support / Bug Reports - Accessible a tous -->
                <div class="flex flex-col gap-1">
                    <p class="px-3 text-xs font-bold text-text-secondary uppercase tracking-wider mb-1">Support</p>
                    <a href="{{ route('bug-reports.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('bug-reports.*') ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-surface-highlight text-text-secondary hover:text-white' }} transition-colors">
                        <span class="material-symbols-outlined text-[20px]">bug_report</span>
                        <p class="text-sm font-medium">Signaler un probleme</p>
                    </a>
                </div>

            </nav>


        </aside>

        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            style="display: none;"></div>

        <!-- Main Content -->
        <main class="flex flex-col flex-1 h-full min-w-0 bg-background-light dark:bg-background-dark relative">
            <!-- Top Header -->
            <header
                class="flex items-center justify-between border-b border-[#3a2e24] px-6 py-4 bg-background-dark/80 backdrop-blur-md sticky top-0 z-20">
                <!-- Mobile Menu Button -->
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-white mr-4">
                    <span class="material-symbols-outlined">menu</span>
                </button>

                <!-- Search -->
                <div class="hidden md:flex flex-1 max-w-md">
                    @livewire('core.global-search')
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-4 ml-auto">
                    @livewire('core.notification-dropdown')

                    <!-- User Menu -->
                    <div class="relative" x-data="{ showUserMenu: false }">
                        <button @click="showUserMenu = !showUserMenu"
                            class="flex items-center gap-2 pl-2 pr-1 py-1 hover:bg-surface-highlight rounded-full transition-colors order-last md:order-none">
                            @if(Auth::user()->avatar)
                                <img src="{{ Storage::url(Auth::user()->avatar) }}"
                                    class="size-8 rounded-full ring-2 ring-primary/30 object-cover"
                                    alt="{{ Auth::user()->name }}">
                            @else
                                <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-8 ring-2 ring-primary/30"
                                    style='background-image: url("https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=f48c25&color=fff");'>
                                </div>
                            @endif
                            <span class="material-symbols-outlined text-text-secondary text-[20px]">expand_more</span>
                        </button>

                        <!-- Dropdown -->
                        <div x-show="showUserMenu" @click.away="showUserMenu = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute top-full right-0 mt-2 w-56 bg-surface-dark border border-[#3a2e24] rounded-xl shadow-lg overflow-hidden z-50">

                            <div class="px-4 py-3 border-b border-[#3a2e24]">
                                <p class="text-white text-sm font-bold truncate">{{ Auth::user()->name }}</p>
                                <p class="text-text-secondary text-xs truncate">{{ Auth::user()->email }}</p>
                            </div>

                            <div class="py-1">
                                <a href="{{ route('profile') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-text-secondary hover:bg-surface-highlight hover:text-white transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">person</span>
                                    <span class="text-sm font-medium">Mon Profil</span>
                                </a>
                                <a href="{{ route('notifications.index') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-text-secondary hover:bg-surface-highlight hover:text-white transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">notifications</span>
                                    <span class="text-sm font-medium">Notifications</span>
                                </a>
                                <div class="border-t border-[#3a2e24] my-1"></div>
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-header"
                                    onsubmit="return handleLogoutHeader(event)">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-3 px-4 py-2.5 text-text-secondary hover:bg-surface-highlight hover:text-red-400 transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">logout</span>
                                        <span class="text-sm font-medium">Deconnexion</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('help') }}"
                        class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg transition-colors"
                        title="Centre d'Aide">
                        <span class="material-symbols-outlined">help</span>
                    </a>
                </div>
            </header>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-8 custom-scrollbar">
                <!-- Flash Messages avec auto-dismiss après 15s -->
                @if (session()->has('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 15000)"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="mb-4 flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined">check_circle</span>
                            <span class="text-sm font-medium">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-green-400/70 hover:text-green-400 transition-colors">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 15000)"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="mb-4 flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined">error</span>
                            <span class="text-sm font-medium">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-red-400/70 hover:text-red-400 transition-colors">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>
                @endif

                {{ $slot }}

                <!-- Footer -->
                <footer
                    class="mt-8 pt-8 border-t border-[#3a2e24] flex flex-col md:flex-row justify-between items-center text-text-secondary text-sm gap-4">
                    <p>© {{ date('Y') }} ERP WMC. Tous droits réservés.</p>
                    <div class="flex gap-6">
                        <a href="#" class="hover:text-primary transition-colors">Support</a>
                        <a href="#" class="hover:text-primary transition-colors">Documentation</a>
                        <a href="#" class="hover:text-primary transition-colors">CGU</a>
                    </div>
                </footer>
            </div>
        </main>
    </div>

    @stack('scripts')

    <script>
        // Gestion du logout avec fallback si le token CSRF expire
        function handleLogout(event) {
            const form = document.getElementById('logout-form');

            // Essayer de soumettre normalement
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            }).then(response => {
                if (response.ok || response.redirected) {
                    window.location.href = '/login';
                } else if (response.status === 419) {
                    // Token CSRF expiré, utiliser la route de secours
                    window.location.href = '/force-logout';
                } else {
                    // Autre erreur, forcer le logout
                    window.location.href = '/force-logout';
                }
            }).catch(() => {
                // En cas d'erreur réseau, forcer le logout
                window.location.href = '/force-logout';
            });

            event.preventDefault();
            return false;
        }

        // Gestion du logout Header
        function handleLogoutHeader(event) {
            const form = document.getElementById('logout-form-header');

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            }).then(response => {
                if (response.ok || response.redirected) {
                    window.location.href = '/login';
                } else {
                    window.location.href = '/login';
                }
            }).catch(() => {
                window.location.href = '/login';
            });

            event.preventDefault();
            return false;
        }

        // Gestion globale des erreurs CSRF (419) pour Livewire
        document.addEventListener('DOMContentLoaded', function () {
            // Intercepter les erreurs Livewire
            document.addEventListener('livewire:init', () => {
                Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status === 419) {
                            preventDefault();
                            // Afficher un message et rafraîchir la page
                            if (confirm('Votre session a expiré. Cliquez OK pour rafraîchir la page.')) {
                                window.location.reload();
                            }
                        }
                    });
                });
            });

            // Rafraîchir le token CSRF automatiquement toutes les 15 minutes
            async function refreshCsrfToken() {
                try {
                    const response = await fetch(window.location.href, {
                        method: 'GET',
                        credentials: 'same-origin'
                    });
                    const html = await response.text();
                    const match = html.match(/<meta name="csrf-token" content="([^"]+)"/);
                    if (match && match[1]) {
                        document.querySelector('meta[name="csrf-token"]').setAttribute('content', match[1]);
                        // Mettre à jour Livewire si disponible
                        if (window.Livewire) {
                            // Le token sera automatiquement utilisé par Livewire
                        }
                    }
                } catch (e) {
                    console.log('Impossible de rafraîchir le token CSRF');
                }
            }

            // Rafraîchir toutes les 15 minutes
            setInterval(refreshCsrfToken, 15 * 60 * 1000);

            // Rafraîchir quand l'utilisateur revient sur l'onglet après une longue absence
            let lastActivity = Date.now();
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) {
                    const inactiveTime = Date.now() - lastActivity;
                    // Si plus de 10 minutes d'inactivité, rafraîchir le token
                    if (inactiveTime > 10 * 60 * 1000) {
                        refreshCsrfToken();
                    }
                }
                lastActivity = Date.now();
            });
        });
    </script>
</body>

</html>