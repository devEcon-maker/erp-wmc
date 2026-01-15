<?php

namespace App\Modules\Inventory\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\ProductCategory;

class ProductsList extends Component
{
    use WithPagination;

    public $search = '';
    public $type = '';
    public $category_id = '';
    public $is_active = '';

    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $queryString = ['search', 'type', 'category_id', 'is_active', 'sortField', 'sortDirection'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleActive($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->is_active = !$product->is_active;
            $product->save();

            $status = $product->is_active ? 'activé' : 'désactivé';
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Produit {$product->name} {$status}."
            ]);
        }
    }

    public function duplicate($id)
    {
        $product = Product::find($id);
        if ($product) {
            $newProduct = $product->replicate();
            $newProduct->reference = $newProduct->reference . '-COPY-' . time();
            $newProduct->name = 'Copie de ' . $newProduct->name;
            $newProduct->is_active = false;
            $newProduct->save();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Produit {$product->name} dupliqué avec succès."
            ]);
        }
    }

    public function render()
    {
        $query = Product::query()
            ->with('category')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('reference', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->category_id, fn($q) => $q->where('category_id', $this->category_id))
            ->when($this->is_active !== '', fn($q) => $q->where('is_active', $this->is_active))
            ->orderBy($this->sortField, $this->sortDirection);

        return view('inventory::livewire.products-list', [
            'products' => $query->paginate(25),
            'categories' => ProductCategory::all(),
        ])->layout('layouts.app');
    }
}
