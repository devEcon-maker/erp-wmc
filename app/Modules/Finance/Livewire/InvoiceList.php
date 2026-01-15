<?php

namespace App\Modules\Finance\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\Finance\Models\Invoice;

class InvoiceList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $due_filter = ''; // 'overdue', 'this_month'

    protected $queryString = ['search', 'status', 'due_filter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getInvoicesProperty()
    {
        return Invoice::query()
            ->with(['contact', 'creator'])
            ->when($this->search, function ($query) {
                $query->where('reference', 'like', '%' . $this->search . '%')
                    ->orWhereHas('contact', function ($q) {
                        $q->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%')
                            ->orWhere('company_name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->due_filter === 'overdue', fn($q) => $q->overdue())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function getStatsProperty()
    {
        return [
            'total_due' => Invoice::unpaid()->sum('total_amount_ttc') - Invoice::unpaid()->sum('paid_amount'), // Approx
            'overdue_count' => Invoice::overdue()->count(),
            'paid_month' => Invoice::where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('total_amount_ttc'),
        ];
    }

    public function processReminders(\App\Modules\Finance\Services\ReminderService $service)
    {
        $count = $service->processReminders();
        session()->flash('success', "Le processus de relance a été exécuté. {$count} rappel(s) envoyé(s).");
    }

    public function render()
    {
        return view('finance::livewire.invoice-list', [
            'invoices' => $this->invoices,
            'stats' => $this->stats
        ])->layout('layouts.app');
    }
}
