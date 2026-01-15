<?php

namespace App\Modules\CRM\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\CRM\Models\Order;
use App\Modules\CRM\Services\OrderService;

class OrderList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $showDeleteModal = false;
    public $orderToDelete = null;

    protected $queryString = ['search', 'status'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getOrdersProperty()
    {
        return Order::query()
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
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function getStatsProperty()
    {
        return [
            'total' => Order::count(),
            'draft' => Order::where('status', 'draft')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];
    }

    public function confirmOrder(Order $order, OrderService $service)
    {
        try {
            $service->confirmOrder($order);
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Commande {$order->reference} confirmée avec succès."
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function cancelOrder(Order $order, OrderService $service)
    {
        try {
            $service->cancelOrder($order);
            $this->dispatch('toast', [
                'type' => 'warning',
                'message' => "Commande {$order->reference} annulée."
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function confirmDelete($orderId)
    {
        $this->orderToDelete = Order::find($orderId);
        $this->showDeleteModal = true;
    }

    public function deleteOrder()
    {
        if ($this->orderToDelete) {
            if (!in_array($this->orderToDelete->status, ['draft', 'cancelled'])) {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'message' => "Seules les commandes en brouillon ou annulées peuvent être supprimées."
                ]);
                $this->showDeleteModal = false;
                $this->orderToDelete = null;
                return;
            }

            $reference = $this->orderToDelete->reference;
            $this->orderToDelete->lines()->delete();
            $this->orderToDelete->delete();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Commande {$reference} supprimée avec succès."
            ]);

            $this->showDeleteModal = false;
            $this->orderToDelete = null;
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->orderToDelete = null;
    }

    public function render()
    {
        return view('crm::livewire.order-list', [
            'orders' => $this->orders,
            'stats' => $this->stats,
        ])->layout('layouts.app');
    }
}
