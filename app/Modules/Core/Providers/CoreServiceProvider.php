<?php

namespace App\Modules\Core\Providers;

use App\Modules\Core\Livewire\Dashboard;
use App\Modules\Core\Livewire\GlobalSearch;
use App\Modules\Core\Livewire\NotificationDropdown;
use App\Modules\Core\Livewire\NotificationsList;
use App\Modules\Core\Livewire\Admin\UserManagement;
use App\Modules\Core\Livewire\Admin\RoleManagement;
use App\Modules\Core\Livewire\Admin\Settings;
use App\Modules\Core\Livewire\BugReportList;
use App\Modules\Core\Livewire\ProfileEdit;
use App\Modules\Core\Livewire\Help;
use App\Modules\Core\Models\SmtpConfiguration;
use App\Modules\Core\Services\DashboardService;
use App\Modules\Core\Services\GlobalSearchService;
use App\Modules\Core\Services\UpdateService;
use App\Modules\Core\Console\Commands\CheckUpdateCommand;
use App\Modules\Core\Console\Commands\BuildUpdateCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

class CoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'core');
        $this->registerLivewireComponents();
        $this->registerCommands();
        $this->configureDefaultMailer();
    }

    private function configureDefaultMailer(): void
    {
        // Charger la configuration SMTP par defaut si la table existe
        try {
            if (Schema::hasTable('smtp_configurations')) {
                $defaultSmtp = SmtpConfiguration::getDefault();
                if ($defaultSmtp) {
                    $defaultSmtp->applyToMailer();
                }
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs lors du boot (migrations pas encore executees)
        }
    }

    public function register(): void
    {
        $this->app->singleton(DashboardService::class, DashboardService::class);
        $this->app->singleton(GlobalSearchService::class, GlobalSearchService::class);
        $this->app->singleton(UpdateService::class, UpdateService::class);
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckUpdateCommand::class,
                BuildUpdateCommand::class,
            ]);
        }
    }

    private function registerLivewireComponents(): void
    {
        Livewire::component('core.dashboard', Dashboard::class);
        Livewire::component('core.global-search', GlobalSearch::class);
        Livewire::component('core.notification-dropdown', NotificationDropdown::class);
        Livewire::component('core.notifications-list', NotificationsList::class);

        // Admin components
        Livewire::component('admin.user-management', UserManagement::class);
        Livewire::component('admin.role-management', RoleManagement::class);
        Livewire::component('admin.settings', Settings::class);

        // Bug Reports
        Livewire::component('core.bug-report-list', BugReportList::class);

        // Profile
        Livewire::component('core.profile-edit', ProfileEdit::class);

        // Help
        Livewire::component('core.help', Help::class);
    }
}

