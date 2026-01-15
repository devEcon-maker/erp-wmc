<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $modules = config('modules.modules');

        if (is_array($modules)) {
            foreach ($modules as $module) {
                // Try the standard path first (Providers/{Module}ServiceProvider)
                $provider = "App\\Modules\\{$module}\\Providers\\{$module}ServiceProvider";
                if (class_exists($provider)) {
                    $this->app->register($provider);
                    continue;
                }

                // Try the root path ({Module}ServiceProvider)
                $provider = "App\\Modules\\{$module}\\{$module}ServiceProvider";
                if (class_exists($provider)) {
                    $this->app->register($provider);
                }
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
