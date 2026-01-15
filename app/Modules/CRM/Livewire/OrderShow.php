<?php

namespace App\Modules\CRM\Livewire;

use Livewire\Component;
use App\Modules\CRM\Models\Order;
use App\Modules\CRM\Services\OrderService;

class OrderShow extends Component
{
    public Order $order;
    public $showDeleteModal = false;

    public function mount(Order $order)
    {
        $this->order = $order->load(['contact', 'lines.product', 'creator']);
    }

    public function confirmOrder(OrderService $service)
    {
        try {
            $service->confirmOrder($this->order);
            $this->order->refresh();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Commande {$this->order->reference} confirmée avec succès."
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function cancelOrder(OrderService $service)
    {
        try {
            $service->cancelOrder($this->order);
            $this->order->refresh();

            $this->dispatch('toast', [
                'type' => 'warning',
                'message' => "Commande {$this->order->reference} annulée."
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function markAsDelivered()
    {
        $this->order->update(['status' => 'delivered', 'delivery_date' => now()]);
        $this->order->refresh();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Commande {$this->order->reference} marquée comme livrée."
        ]);
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    public function deleteOrder()
    {
        if (!in_array($this->order->status, ['draft', 'cancelled'])) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => "Seules les commandes en brouillon ou annulées peuvent être supprimées."
            ]);
            $this->showDeleteModal = false;
            return;
        }

        $reference = $this->order->reference;
        $this->order->lines()->delete();
        $this->order->delete();

        session()->flash('success', "Commande {$reference} supprimée avec succès.");
        $this->js('window.location.href = "' . route('crm.orders.index') . '"');
    }

    public function render()
    {
        return view('crm::livewire.order-show')->layout('layouts.app');
    }
}
