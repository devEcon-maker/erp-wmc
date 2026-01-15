<?php

namespace App\Modules\CRM\Providers;

use Livewire\Livewire;
use App\Modules\CRM\Livewire\ContactsList;
use App\Modules\CRM\Livewire\ContactForm;
use App\Modules\CRM\Livewire\ContactShow;
use App\Modules\CRM\Livewire\OpportunityPipeline;
use App\Modules\CRM\Livewire\OpportunityForm;
use App\Modules\CRM\Livewire\OpportunityShow;
use App\Modules\CRM\Livewire\OrderList;
use App\Modules\CRM\Livewire\OrderForm;
use App\Modules\CRM\Livewire\OrderShow;
use App\Modules\CRM\Livewire\ProposalList;
use App\Modules\CRM\Livewire\ProposalForm;
use App\Modules\CRM\Livewire\ProposalShow;
use App\Modules\CRM\Livewire\ContractList;
use App\Modules\CRM\Livewire\ContractForm;
use App\Modules\CRM\Livewire\ContractShow;
use App\Modules\CRM\Livewire\ReminderList;
use Illuminate\Support\ServiceProvider;

class CRMServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'crm');

        // Register Livewire Components
        Livewire::component('crm.contacts-list', ContactsList::class);
        Livewire::component('crm.contact-form', ContactForm::class);
        Livewire::component('crm.contact-show', ContactShow::class);

        Livewire::component('crm.opportunity-pipeline', OpportunityPipeline::class);
        Livewire::component('crm.opportunity-form', OpportunityForm::class);
        Livewire::component('crm.opportunity-show', OpportunityShow::class);

        Livewire::component('crm.proposal-list', ProposalList::class);
        Livewire::component('crm.proposal-form', ProposalForm::class);
        Livewire::component('crm.proposal-show', ProposalShow::class);

        Livewire::component('crm.order-list', OrderList::class);
        Livewire::component('crm.order-form', OrderForm::class);
        Livewire::component('crm.order-show', OrderShow::class);

        Livewire::component('crm.contract-list', ContractList::class);
        Livewire::component('crm.contract-form', ContractForm::class);
        Livewire::component('crm.contract-show', ContractShow::class);

        Livewire::component('crm.reminder-list', ReminderList::class);
    }

    public function register(): void
    {
        //
    }
}

