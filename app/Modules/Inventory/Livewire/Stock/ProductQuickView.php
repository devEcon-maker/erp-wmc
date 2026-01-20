<?php

namespace App\Modules\Inventory\Livewire\Stock;

use App\Modules\Inventory\Models\Product;
use Livewire\Component;
use Livewire\Attributes\On;

class ProductQuickView extends Component
{
    public $product = null;
    public $show = false;

    public function openQuickView($productId)
    {
        $this->product = Product::find($productId);
        $this->show = true;

        // Dispatch browser event to open modal
        $this->dispatch('open-modal', name: 'product-quick-view');
    }

    public function closeModal()
    {
        $this->show = false;
        $this->product = null;
        $this->dispatch('close-modal');
    }

    public function render()
    {
        return view('livewire.inventory.stock.product-quick-view', [
            'product' => $this->product,
        ]);
    }
}
