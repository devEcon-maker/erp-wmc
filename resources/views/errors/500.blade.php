<!DOCTYPE html>
<html lang="fr" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur serveur - ERP WMC</title>
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
                class="absolute inset-0 z-10 bg-gradient-to-t from-background-dark via-background-dark/80 to-yellow-500/20 mix-blend-multiply">
            </div>
            <div class="absolute inset-0 z-10 bg-black/40"></div>
            <!-- Content -->
            <div class="relative z-20 max-w-2xl">
                <div
                    class="mb-6 h-12 w-12 rounded-lg bg-yellow-500 flex items-center justify-center shadow-lg shadow-yellow-900/20">
                    <span class="material-symbols-outlined text-white text-3xl">engineering</span>
                </div>
                <h1 class="text-white text-4xl font-bold leading-tight mb-4 tracking-tight">Oups ! Quelque chose s'est mal passe.</h1>
                <p class="text-text-muted text-lg leading-relaxed max-w-lg">
                    Notre equipe technique a ete alertee et travaille sur la resolution du probleme.
                </p>
            </div>
        </div>
        <!-- Right Side: Error Content -->
        <div
            class="flex flex-1 flex-col justify-center items-center p-4 sm:p-12 bg-background-light dark:bg-background-dark relative">
            <div class="w-full max-w-[480px] flex flex-col gap-8 items-center text-center">
                <!-- Mobile Logo (Visible only on small screens) -->
                <div class="lg:hidden mb-4 flex justify-center">
                    <div class="h-12 w-12 rounded-lg bg-yellow-500 flex items-center justify-center shadow-lg">
                        <span class="material-symbols-outlined text-white text-3xl">engineering</span>
                    </div>
                </div>

                <!-- Error Code with Animation -->
                <div class="relative">
                    <div class="text-[150px] sm:text-[200px] font-black text-yellow-500/10 leading-none select-none">
                        500
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="p-6 bg-yellow-500/20 rounded-full animate-pulse">
                            <span class="material-symbols-outlined text-yellow-500 text-6xl sm:text-7xl">error</span>
                        </div>
                    </div>
                </div>

                <!-- Page Heading Component -->
                <div class="flex flex-col gap-3 text-center">
                    <p class="text-gray-900 dark:text-white tracking-tight text-[28px] sm:text-[32px] font-bold leading-tight">
                        Erreur serveur
                    </p>
                    <p class="text-gray-500 dark:text-text-muted text-sm font-normal leading-normal max-w-sm mx-auto">
                        Une erreur inattendue s'est produite. Veuillez reessayer dans quelques instants.
                    </p>
                </div>

                <!-- Info Box -->
                <div class="w-full bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/30 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-yellow-500 text-xl mt-0.5">tips_and_updates</span>
                        <div class="text-sm text-yellow-700 dark:text-yellow-400 text-left">
                            <p class="font-medium mb-1">Que faire ?</p>
                            <ul class="text-yellow-600 dark:text-yellow-300 space-y-1">
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[14px]">refresh</span>
                                    Rafraichissez la page
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[14px]">schedule</span>
                                    Attendez quelques minutes
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[14px]">support</span>
                                    Contactez le support si le probleme persiste
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4 w-full mt-2">
                    <button onclick="location.reload()"
                        class="flex flex-1 min-w-[84px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-12 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-primary/90 transition-colors shadow-md">
                        <span class="material-symbols-outlined text-xl">refresh</span>
                        <span class="truncate">Rafraichir</span>
                    </button>
                    <a href="{{ url('/') }}"
                        class="flex flex-1 min-w-[84px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-12 px-5 bg-transparent border border-gray-300 dark:border-border-dark text-gray-900 dark:text-white text-base font-medium leading-normal tracking-[0.015em] hover:bg-gray-100 dark:hover:bg-input-dark transition-colors">
                        <span class="material-symbols-outlined text-xl">home</span>
                        <span class="truncate">Accueil</span>
                    </a>
                </div>

                <!-- Contact Support -->
                <div class="w-full pt-6 border-t border-gray-300 dark:border-border-dark">
                    <p class="text-gray-500 dark:text-text-muted text-sm mb-3">
                        Besoin d'aide ?
                    </p>
                    <a href="mailto:support@erp-wmc.com"
                        class="inline-flex items-center gap-2 text-primary hover:text-primary/80 transition-colors text-sm font-medium">
                        <span class="material-symbols-outlined text-lg">support_agent</span>
                        Contacter le support technique
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
