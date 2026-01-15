<?php

namespace App\Modules\Inventory\Livewire\Purchases;

use App\Modules\Inventory\Models\PurchaseOrder;
use App\Modules\CRM\Models\Contact;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrdersList extends Component
{
    use WithPagination;

    public $status = '';
    public $supplierId = '';
    public $search = '';

    protected $queryString = [
        'status' => ['except' => ''],
        'supplierId' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function render()
    {
        $orders = PurchaseOrder::query()
            ->with(['supplier'])
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->supplierId, fn($q) => $q->where('supplier_id', $this->supplierId))
            ->when($this->search, fn($q) => $q->where('reference', 'like', '%' . $this->search . '%'))
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('livewire.inventory.purchases.purchase-orders-list', [
            'orders' => $orders,
            'suppliers' => Contact::where('type', 'fournisseur')->orderBy('company_name')->get(),
        ])->layout('layouts.app');
    }
}
