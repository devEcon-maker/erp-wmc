<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
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
                <span class="material-symbols-outlined text-white text-3xl">lock_reset</span>
            </div>
            <h1 class="text-white text-4xl font-bold leading-tight mb-4 tracking-tight">Recuperez votre acces en toute
                securite.</h1>
            <p class="text-text-muted text-lg leading-relaxed max-w-lg">
                Un lien de reinitialisation vous sera envoye par email pour definir un nouveau mot de passe.
            </p>
        </div>
    </div>
    <!-- Right Side: Forgot Password Form -->
    <div
        class="flex flex-1 flex-col justify-center items-center p-4 sm:p-12 bg-background-light dark:bg-background-dark relative">
        <div class="w-full max-w-[480px] flex flex-col gap-8">
            <!-- Mobile Logo (Visible only on small screens) -->
            <div class="lg:hidden mb-4 flex justify-center">
                <div class="h-12 w-12 rounded-lg bg-primary flex items-center justify-center shadow-lg">
                    <span class="material-symbols-outlined text-white text-3xl">lock_reset</span>
                </div>
            </div>
            <!-- Page Heading Component -->
            <div class="flex flex-col gap-3 text-center lg:text-left">
                <p class="text-gray-900 dark:text-white tracking-tight text-[32px] font-bold leading-tight">Mot de passe oublie ?
                </p>
                <p class="text-gray-500 dark:text-text-muted text-sm font-normal leading-normal">Entrez votre adresse email et nous vous enverrons un lien pour reinitialiser votre mot de passe</p>
            </div>

            <!-- Session Status (Success Message) -->
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-green-500 text-xl mt-0.5">check_circle</span>
                        <div class="text-sm text-green-700 dark:text-green-400">
                            <p class="font-medium">Email envoye !</p>
                            <p class="text-green-600 dark:text-green-300">{{ session('status') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form Fields -->
            <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
                <!-- Email Field -->
                <label class="flex flex-col w-full">
                    <p class="text-gray-900 dark:text-white text-base font-medium leading-normal pb-2">Adresse email</p>
                    <div class="flex w-full flex-1 items-stretch rounded-lg shadow-sm">
                        <input wire:model="email" id="email" type="email" required autofocus
                            class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-l-lg text-gray-900 dark:text-white focus:outline-0 focus:ring-0 border border-gray-300 dark:border-border-dark bg-white dark:bg-input-dark focus:border-primary dark:focus:border-primary h-14 placeholder:text-gray-400 dark:placeholder:text-text-muted p-[15px] border-r-0 pr-2 text-base font-normal leading-normal transition-colors"
                            placeholder="exemple@domaine.com" />
                        <div
                            class="text-gray-400 dark:text-text-muted flex border border-gray-300 dark:border-border-dark bg-white dark:bg-input-dark items-center justify-center pr-[15px] rounded-r-lg border-l-0">
                            <span class="material-symbols-outlined">mail</span>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </label>

                <!-- Info Box -->
                <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-blue-500 text-xl mt-0.5">info</span>
                        <div class="text-sm text-blue-700 dark:text-blue-400">
                            <p class="font-medium mb-1">Comment ca marche ?</p>
                            <p class="text-blue-600 dark:text-blue-300">Vous recevrez un email contenant un lien securise. Ce lien est valide pendant 60 minutes.</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-4 mt-2">
                    <button type="submit"
                        class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-12 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-primary/90 transition-colors shadow-md">
                        <span class="material-symbols-outlined text-xl">send</span>
                        <span class="truncate">Envoyer le lien</span>
                    </button>
                    <div class="relative flex py-2 items-center">
                        <div class="flex-grow border-t border-gray-300 dark:border-border-dark"></div>
                        <span class="flex-shrink-0 mx-4 text-gray-500 dark:text-text-muted text-xs">OU</span>
                        <div class="flex-grow border-t border-gray-300 dark:border-border-dark"></div>
                    </div>
                    <a href="{{ route('login') }}" wire:navigate
                        class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-12 px-5 bg-transparent border border-gray-300 dark:border-border-dark text-gray-900 dark:text-white text-base font-medium leading-normal tracking-[0.015em] hover:bg-gray-100 dark:hover:bg-input-dark transition-colors">
                        <span class="material-symbols-outlined text-xl">arrow_back</span>
                        <span class="truncate">Retour a la connexion</span>
                    </a>
                </div>
            </form>
            <!-- Security/Footer Note -->
            <div class="flex items-center justify-center gap-2 mt-auto pt-8 opacity-60">
                <span class="material-symbols-outlined text-gray-400 dark:text-text-muted text-lg">lock</span>
                <p class="text-xs text-gray-400 dark:text-text-muted text-center">
                    Connexion securisee via protocole SSL 256-bit
                </p>
            </div>
        </div>
    </div>
</div>
