<?php

namespace App\Modules\Inventory\Livewire\Purchases;

use App\Modules\Inventory\Models\PurchaseOrder;
use App\Modules\Inventory\Services\PurchaseOrderService;
use Livewire\Component;

class PurchaseOrderShow extends Component
{
    public PurchaseOrder $purchaseOrder;
    public $receivedQuantities = [];

    public function mount(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder->load(['supplier', 'lines.product']);

        // Initialize receivedQuantities for partial reception
        foreach ($this->purchaseOrder->lines as $line) {
            $remaining = max(0, $line->quantity - $line->received_qty);
            $this->receivedQuantities[$line->id] = $remaining;
        }
    }

    public function send(PurchaseOrderService $service)
    {
        if ($service->send($this->purchaseOrder)) {
            $this->dispatch('notify', message: 'Commande envoyée avec succès', type: 'success');
        }
    }

    public function receiveAll(PurchaseOrderService $service)
    {
        $service->receiveAll($this->purchaseOrder);
        $this->dispatch('notify', message: 'Commande réceptionnée entièrement', type: 'success');
        $this->mount($this->purchaseOrder); // Refresh
    }

    public function receivePartial(PurchaseOrderService $service)
    {
        $service->receivePartial($this->purchaseOrder, $this->receivedQuantities);
        $this->dispatch('notify', message: 'Réception partielle enregistrée', type: 'success');
        $this->mount($this->purchaseOrder); // Refresh
    }

    public function cancel(PurchaseOrderService $service)
    {
        if ($service->cancel($this->purchaseOrder)) {
            $this->dispatch('notify', message: 'Commande annulée', type: 'success');
            $this->mount($this->purchaseOrder);
        } else {
            $this->dispatch('notify', message: 'Impossible d\'annuler la commande', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.inventory.purchases.purchase-order-show')->layout('layouts.app');
    }
}
