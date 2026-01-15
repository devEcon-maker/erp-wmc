<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

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
                <span class="material-symbols-outlined text-white text-3xl">grid_view</span>
            </div>
            <h1 class="text-white text-4xl font-bold leading-tight mb-4 tracking-tight">Gérez votre entreprise en toute
                sérénité.</h1>
            <p class="text-text-muted text-lg leading-relaxed max-w-lg">
                Une solution unique pour la gestion client, les ressources humaines, la finance et la productivité.
            </p>
        </div>
    </div>
    <!-- Right Side: Login Form -->
    <div
        class="flex flex-1 flex-col justify-center items-center p-4 sm:p-12 bg-background-light dark:bg-background-dark relative">
        <div class="w-full max-w-[480px] flex flex-col gap-8">
            <!-- Mobile Logo (Visible only on small screens) -->
            <div class="lg:hidden mb-4 flex justify-center">
                <div class="h-12 w-12 rounded-lg bg-primary flex items-center justify-center shadow-lg">
                    <span class="material-symbols-outlined text-white text-3xl">grid_view</span>
                </div>
            </div>
            <!-- Page Heading Component -->
            <div class="flex flex-col gap-3 text-center lg:text-left">
                <p class="text-gray-900 dark:text-white tracking-tight text-[32px] font-bold leading-tight">Bienvenue
                </p>
                <p class="text-gray-500 dark:text-text-muted text-sm font-normal leading-normal">Accédez à votre espace
                    de gestion</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Error Message (compte désactivé, etc.) -->
            @if(session('error'))
                <div class="mb-4 p-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-red-500">error</span>
                        <p class="text-sm font-medium text-red-700 dark:text-red-400">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Form Fields -->
            <form wire:submit="login" class="flex flex-col gap-6">
                <!-- Email Field -->
                <label class="flex flex-col w-full">
                    <p class="text-gray-900 dark:text-white text-base font-medium leading-normal pb-2">Email</p>
                    <div class="flex w-full flex-1 items-stretch rounded-lg shadow-sm">
                        <input wire:model="form.email" id="email" type="email" required autofocus
                            class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-l-lg text-gray-900 dark:text-white focus:outline-0 focus:ring-0 border border-gray-300 dark:border-border-dark bg-white dark:bg-input-dark focus:border-primary dark:focus:border-primary h-14 placeholder:text-gray-400 dark:placeholder:text-text-muted p-[15px] border-r-0 pr-2 text-base font-normal leading-normal transition-colors"
                            placeholder="exemple@domaine.com" />
                        <div
                            class="text-gray-400 dark:text-text-muted flex border border-gray-300 dark:border-border-dark bg-white dark:bg-input-dark items-center justify-center pr-[15px] rounded-r-lg border-l-0">
                            <span class="material-symbols-outlined">person</span>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </label>

                <!-- Password Field -->
                <label class="flex flex-col w-full" x-data="{ showPassword: false }">
                    <div class="flex justify-between items-center pb-2">
                        <p class="text-gray-900 dark:text-white text-base font-medium leading-normal">Mot de passe</p>
                    </div>
                    <div class="flex w-full flex-1 items-stretch rounded-lg shadow-sm">
                        <input wire:model="form.password" id="password"
                            :type="showPassword ? 'text' : 'password'" required
                            class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-l-lg text-gray-900 dark:text-white focus:outline-0 focus:ring-0 border border-gray-300 dark:border-border-dark bg-white dark:bg-input-dark focus:border-primary dark:focus:border-primary h-14 placeholder:text-gray-400 dark:placeholder:text-text-muted p-[15px] border-r-0 pr-2 text-base font-normal leading-normal transition-colors"
                            placeholder="••••••••" />
                        <button type="button"
                            @click="showPassword = !showPassword"
                            class="text-gray-400 dark:text-text-muted flex border border-gray-300 dark:border-border-dark bg-white dark:bg-input-dark items-center justify-center pr-[15px] pl-[10px] rounded-r-lg border-l-0 cursor-pointer hover:text-primary transition-colors focus:outline-none">
                            <span x-show="!showPassword" class="material-symbols-outlined">visibility_off</span>
                            <span x-show="showPassword" class="material-symbols-outlined">visibility</span>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                </label>

                <!-- Utilities: Remember Me + Forgot Password -->
                <div class="flex flex-wrap items-center justify-between gap-y-2">
                    <label class="flex gap-x-3 items-center cursor-pointer pb-2">
                        <input wire:model="form.remember" id="remember" type="checkbox"
                            class="h-5 w-5 rounded border-gray-300 dark:border-border-dark border-2 bg-white dark:bg-transparent text-primary checked:bg-primary checked:border-primary focus:ring-0 focus:ring-offset-0 focus:border-primary focus:outline-none transition-all" />
                        <p class="text-gray-700 dark:text-white text-sm font-normal leading-normal">Se souvenir de moi
                        </p>
                    </label>
                    <a class="text-sm font-medium text-primary hover:text-primary/80 transition-colors"
                        href="{{ route('password.request') }}" wire:navigate>
                        Mot de passe oublié ?
                    </a>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-4 mt-2" x-data="{ showContactModal: false }">
                    <button type="submit"
                        class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-primary/90 transition-colors shadow-md">
                        <span class="truncate">Se connecter</span>
                    </button>
                    <div class="relative flex py-2 items-center">
                        <div class="flex-grow border-t border-gray-300 dark:border-border-dark"></div>
                        <span class="flex-shrink-0 mx-4 text-gray-500 dark:text-text-muted text-xs">OU</span>
                        <div class="flex-grow border-t border-gray-300 dark:border-border-dark"></div>
                    </div>
                    <button type="button"
                        @click="showContactModal = true"
                        class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-5 bg-transparent border border-gray-300 dark:border-border-dark text-gray-900 dark:text-white text-base font-medium leading-normal tracking-[0.015em] hover:bg-gray-100 dark:hover:bg-input-dark transition-colors">
                        <span class="truncate">Creer un compte</span>
                    </button>

                    <!-- Modal Contact Admin -->
                    <div x-show="showContactModal"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 z-50 flex items-center justify-center p-4"
                        style="display: none;">
                        <!-- Backdrop -->
                        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showContactModal = false"></div>

                        <!-- Modal Content -->
                        <div x-show="showContactModal"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="relative bg-white dark:bg-surface-dark rounded-2xl shadow-2xl max-w-md w-full p-6 border border-gray-200 dark:border-border-dark">

                            <!-- Icon -->
                            <div class="flex justify-center mb-4">
                                <div class="p-4 bg-primary/10 rounded-full">
                                    <span class="material-symbols-outlined text-primary text-4xl">admin_panel_settings</span>
                                </div>
                            </div>

                            <!-- Title -->
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white text-center mb-2">
                                Creation de compte
                            </h3>

                            <!-- Message -->
                            <p class="text-gray-600 dark:text-text-muted text-center mb-6">
                                Pour obtenir vos identifiants de connexion, veuillez contacter l'administrateur de votre entreprise.
                            </p>

                            <!-- Info Box -->
                            <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg p-4 mb-6">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-blue-500 text-xl mt-0.5">info</span>
                                    <div class="text-sm text-blue-700 dark:text-blue-400">
                                        <p class="font-medium mb-1">Pourquoi ?</p>
                                        <p class="text-blue-600 dark:text-blue-300">Pour des raisons de securite, seul l'administrateur peut creer des comptes utilisateurs et attribuer les permissions appropriees.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Close Button -->
                            <button type="button"
                                @click="showContactModal = false"
                                class="w-full flex items-center justify-center gap-2 h-12 px-5 bg-primary text-white text-base font-bold rounded-lg hover:bg-primary/90 transition-colors">
                                <span class="material-symbols-outlined text-xl">check</span>
                                J'ai compris
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <!-- Security/Footer Note -->
            <div class="flex items-center justify-center gap-2 mt-auto pt-8 opacity-60">
                <span class="material-symbols-outlined text-gray-400 dark:text-text-muted text-lg">lock</span>
                <p class="text-xs text-gray-400 dark:text-text-muted text-center">
                    Connexion sécurisée via protocole SSL 256-bit
                </p>
            </div>
        </div>
    </div>
</div>