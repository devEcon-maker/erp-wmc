<!DOCTYPE html>
<html lang="fr" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance en cours - ERP WMC</title>
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
                class="absolute inset-0 z-10 bg-gradient-to-t from-background-dark via-background-dark/80 to-blue-500/20 mix-blend-multiply">
            </div>
            <div class="absolute inset-0 z-10 bg-black/40"></div>
            <!-- Content -->
            <div class="relative z-20 max-w-2xl">
                <div
                    class="mb-6 h-12 w-12 rounded-lg bg-blue-500 flex items-center justify-center shadow-lg shadow-blue-900/20">
                    <span class="material-symbols-outlined text-white text-3xl">build</span>
                </div>
                <h1 class="text-white text-4xl font-bold leading-tight mb-4 tracking-tight">Ameliorations en cours.</h1>
                <p class="text-text-muted text-lg leading-relaxed max-w-lg">
                    Nous mettons a jour votre ERP pour vous offrir une meilleure experience. Merci de votre patience.
                </p>
            </div>
        </div>
        <!-- Right Side: Maintenance Content -->
        <div
            class="flex flex-1 flex-col justify-center items-center p-4 sm:p-12 bg-background-light dark:bg-background-dark relative">
            <div class="w-full max-w-[480px] flex flex-col gap-8 items-center text-center">
                <!-- Mobile Logo (Visible only on small screens) -->
                <div class="lg:hidden mb-4 flex justify-center">
                    <div class="h-12 w-12 rounded-lg bg-blue-500 flex items-center justify-center shadow-lg">
                        <span class="material-symbols-outlined text-white text-3xl">build</span>
                    </div>
                </div>

                <!-- Maintenance Icon with Animation -->
                <div class="relative">
                    <div class="text-[150px] sm:text-[200px] font-black text-blue-500/10 leading-none select-none">
                        503
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="p-6 bg-blue-500/20 rounded-full">
                            <span class="material-symbols-outlined text-blue-500 text-6xl sm:text-7xl animate-spin" style="animation-duration: 3s;">settings</span>
                        </div>
                    </div>
                </div>

                <!-- Page Heading Component -->
                <div class="flex flex-col gap-3 text-center">
                    <p class="text-gray-900 dark:text-white tracking-tight text-[28px] sm:text-[32px] font-bold leading-tight">
                        Maintenance en cours
                    </p>
                    <p class="text-gray-500 dark:text-text-muted text-sm font-normal leading-normal max-w-sm mx-auto">
                        L'application est temporairement indisponible pour des travaux de maintenance.
                    </p>
                </div>

                <!-- Progress Animation -->
                <div class="w-full">
                    <div class="bg-surface-highlight rounded-full h-2 overflow-hidden">
                        <div class="bg-blue-500 h-full rounded-full animate-pulse" style="width: 60%;"></div>
                    </div>
                    <p class="text-text-muted text-xs mt-2">Mise a jour en cours...</p>
                </div>

                <!-- Info Box -->
                <div class="w-full bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-blue-500 text-xl mt-0.5">schedule</span>
                        <div class="text-sm text-blue-700 dark:text-blue-400 text-left">
                            <p class="font-medium mb-1">Duree estimee</p>
                            <p class="text-blue-600 dark:text-blue-300">
                                La maintenance devrait durer quelques minutes. L'application sera de nouveau disponible tres bientot.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- What's being updated -->
                <div class="w-full bg-surface-highlight rounded-lg p-4">
                    <p class="text-white text-sm font-medium mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">update</span>
                        Ce que nous ameliorons:
                    </p>
                    <div class="grid grid-cols-2 gap-2 text-text-muted text-xs">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-400 text-[14px]">check_circle</span>
                            Performances
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-400 text-[14px]">check_circle</span>
                            Securite
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-400 text-[14px] animate-spin">sync</span>
                            Fonctionnalites
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-400 text-[14px]">pending</span>
                            Optimisations
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-4 w-full mt-2">
                    <button onclick="location.reload()"
                        class="flex w-full cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-12 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-primary/90 transition-colors shadow-md">
                        <span class="material-symbols-outlined text-xl">refresh</span>
                        <span class="truncate">Verifier la disponibilite</span>
                    </button>
                </div>

                <!-- Security/Footer Note -->
                <div class="flex items-center justify-center gap-2 mt-auto pt-8 opacity-60">
                    <span class="material-symbols-outlined text-gray-400 dark:text-text-muted text-lg">grid_view</span>
                    <p class="text-xs text-gray-400 dark:text-text-muted text-center">
                        ERP WMC - Merci de votre patience
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
