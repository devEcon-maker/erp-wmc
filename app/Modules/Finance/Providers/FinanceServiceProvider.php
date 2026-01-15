<?php

namespace App\Modules\Finance\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use App\Modules\Finance\Livewire\InvoiceList;
use App\Modules\Finance\Livewire\InvoiceForm;
use App\Modules\Finance\Livewire\InvoiceShow;
use App\Modules\Finance\Livewire\SubscriptionList;

class FinanceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'finance');

        // Register Livewire Components
        Livewire::component('finance.invoice-list', InvoiceList::class);
        Livewire::component('finance.invoice-form', InvoiceForm::class);
        Livewire::component('finance.invoice-show', InvoiceShow::class);
        Livewire::component('finance.subscription-list', SubscriptionList::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Modules\Finance\Console\Commands\SendPaymentReminders::class,
            ]);
        }
    }

    public function register(): void
    {

    }
}
