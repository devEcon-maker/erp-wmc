<?php

namespace App\Modules\Inventory\Livewire;

use Livewire\Component;
use App\Modules\Inventory\Models\Product;

class ProductShow extends Component
{
    public Product $product;
    public $showDeleteModal = false;

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    public function delete()
    {
        $name = $this->product->name;
        $this->product->delete();

        session()->flash('success', "Produit {$name} supprimé avec succès.");
        $this->js('window.location.href = "' . route('inventory.products.index') . '"');
    }

    public function render()
    {
        return view('inventory::livewire.product-show')->layout('layouts.app');
    }
}
