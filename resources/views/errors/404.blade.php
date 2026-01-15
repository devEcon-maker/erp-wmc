<!DOCTYPE html>
<html lang="fr" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page introuvable - ERP WMC</title>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?display=swap&family=Inter:wght@400;500;600;700;900&family=Noto+Sans:wght@400;500;700;900">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-background-dark min-h-screen">
    <div class="flex min-h-screen w-full flex-row">
        <!-- Left Side: Visual/Brand (Hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-7/12 relative flex-col justify-end p-12 overflow-hidden bg-surface-dark">
            <!-- Background Image -->
            <div class="absolute inset-0 z-0 bg-cover bg-center"
                data-alt="Modern abstract corporate architecture with glass patterns reflecting dark orange lights"
                style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuABy2OGKXH-XOhtvvg_DUCcIzhZDi7ZYz7IGCOuLfWYLSDJDkRkIVVwxZjtfbZ12sMM0AmJEaXttxCWpqCHTMTLw4pnpL7nzVbeJG_bHvSnFfwgJq48UFsQ9jFnhZZy1XxMrRrWRNyG-JdPWtt75kn6LpByTJlFlI0Ta-2ugcoCB1c9H2j47dyulaKS02BWvyygCmWKYEqWGT-VrNLaeje-Clswsi7ELH3OhdUHjJi9I3vh21_esXAUvnK6OI7LsQePjiO79SlxTYmB");'>
            </div>
            <!-- Overlay Gradient -->
            <div
                class="absolute inset-0 z-10 bg-gradient-to-t from-background-dark via-background-dark/80 to-primary/20 mix-blend-multiply">
            </div>
            <div class="absolute inset-0 z-10 bg-black/40"></div>
            <!-- Content -->
            <div class="relative z-20 max-w-2xl">
                <div
                    class="mb-6 h-12 w-12 rounded-lg bg-primary flex items-center justify-center shadow-lg shadow-orange-900/20">
                    <span class="material-symbols-outlined text-white text-3xl">explore_off</span>
                </div>
                <h1 class="text-white text-4xl font-bold leading-tight mb-4 tracking-tight">Cette page semble avoir
                    disparu.</h1>
                <p class="text-text-muted text-lg leading-relaxed max-w-lg">
                    Ne vous inquietez pas, votre ERP fonctionne parfaitement. Retournez a l'accueil pour continuer.
                </p>
            </div>
        </div>
        <!-- Right Side: Error Content -->
        <div
            class="flex flex-1 flex-col justify-center items-center p-4 sm:p-12 bg-background-light dark:bg-background-dark relative">
            <div class="w-full max-w-[480px] flex flex-col gap-8 items-center text-center">
                <!-- Mobile Logo (Visible only on small screens) -->
                <div class="lg:hidden mb-4 flex justify-center">
                    <div class="h-12 w-12 rounded-lg bg-primary flex items-center justify-center shadow-lg">
                        <span class="material-symbols-outlined text-white text-3xl">explore_off</span>
                    </div>
                </div>

                <!-- Error Code with Animation -->
                <div class="relative">
                    <div class="text-[150px] sm:text-[200px] font-black text-primary/10 leading-none select-none">
                        404
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="p-6 bg-primary/20 rounded-full animate-pulse">
                            <span class="material-symbols-outlined text-primary text-6xl sm:text-7xl">search_off</span>
                        </div>
                    </div>
                </div>

                <!-- Page Heading Component -->
                <div class="flex flex-col gap-3 text-center">
                    <p class="text-gray-900 dark:text-white tracking-tight text-[28px] sm:text-[32px] font-bold leading-tight">
                        Page introuvable
                    </p>
                    <p class="text-gray-500 dark:text-text-muted text-sm font-normal leading-normal max-w-sm mx-auto">
                        La page que vous recherchez n'existe pas ou a ete deplacee vers une autre adresse.
                    </p>
                </div>

                <!-- Suggestions Box -->
                <div class="w-full bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-blue-500 text-xl mt-0.5">lightbulb</span>
                        <div class="text-sm text-blue-700 dark:text-blue-400 text-left">
                            <p class="font-medium mb-2">Suggestions:</p>
                            <ul class="text-blue-600 dark:text-blue-300 space-y-1">
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[14px]">check</span>
                                    Verifiez que l'URL est correcte
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[14px]">check</span>
                                    Utilisez le menu de navigation
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[14px]">check</span>
                                    Retournez a la page d'accueil
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4 w-full mt-2">
                    <a href="{{ url('/') }}"
                        class="flex flex-1 min-w-[84px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-12 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-primary/90 transition-colors shadow-md">
                        <span class="material-symbols-outlined text-xl">home</span>
                        <span class="truncate">Accueil</span>
                    </a>
                    <button onclick="history.back()"
                        class="flex flex-1 min-w-[84px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-12 px-5 bg-transparent border border-gray-300 dark:border-border-dark text-gray-900 dark:text-white text-base font-medium leading-normal tracking-[0.015em] hover:bg-gray-100 dark:hover:bg-input-dark transition-colors">
                        <span class="material-symbols-outlined text-xl">arrow_back</span>
                        <span class="truncate">Retour</span>
                    </button>
                </div>

                <!-- Contact Support -->
                <div class="w-full pt-6 border-t border-gray-300 dark:border-border-dark">
                    <p class="text-gray-500 dark:text-text-muted text-sm mb-3">
                        Le probleme persiste ?
                    </p>
                    <a href="mailto:support@erp-wmc.com"
                        class="inline-flex items-center gap-2 text-primary hover:text-primary/80 transition-colors text-sm font-medium">
                        <span class="material-symbols-outlined text-lg">support_agent</span>
                        Contacter le support
                    </a>
                </div>

                <!-- Security/Footer Note -->
                <div class="flex items-center justify-center gap-2 mt-auto pt-8 opacity-60">
                    <span class="material-symbols-outlined text-gray-400 dark:text-text-muted text-lg">grid_view</span>
                    <p class="text-xs text-gray-400 dark:text-text-muted text-center">
                        ERP WMC - Gestion d'entreprise
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
