<?php

namespace App\Modules\Inventory\Livewire;

use Livewire\Component;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\ProductCategory;
use Illuminate\Validation\Rule;

class ProductForm extends Component
{
    public ?Product $product = null;

    public $type = 'product';
    public $reference;
    public $name;
    public $description;
    public $category_id;
    public $purchase_price = 0;
    public $selling_price = 0;
    public $tax_rate = 20.00;
    public $unit = 'unité';
    public $is_active = true;
    public $track_stock = true;
    public $min_stock_alert = 0;

    public function mount(Product $product = null)
    {
        if ($product && $product->exists) {
            $this->product = $product;
            $this->fill($product->toArray());
        } else {
            $this->product = new Product();
            // Auto-generate reference draft? Or leave empty.
        }
    }

    public function rules()
    {
        return [
            'type' => 'required|in:product,service',
            'reference' => ['required', 'string', 'max:255', Rule::unique('products', 'reference')->ignore($this->product->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:product_categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'is_active' => 'boolean',
            'track_stock' => 'boolean',
            'min_stock_alert' => 'nullable|integer|min:0',
        ];
    }

    public function getMarginProperty()
    {
        return (float) $this->selling_price - (float) $this->purchase_price;
    }

    public function getMarginPercentProperty()
    {
        if ((float) $this->purchase_price > 0) {
            return ($this->margin / (float) $this->purchase_price) * 100;
        }
        return 100;
    }

    public function updatedName($value)
    {
        // Auto-generate reference if empty
        if (empty($this->reference) && !$this->product->exists) {
            $this->reference = 'REF-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $value), 0, 3)) . '-' . date('ymd');
        }
    }

    public function save()
    {
        $this->validate();

        $isNew = !$this->product->exists;

        $this->product->fill($this->except('product', 'margin', 'margin_percent'));
        $this->product->save();

        $message = $isNew
            ? "Produit {$this->product->name} créé avec succès."
            : "Produit {$this->product->name} mis à jour avec succès.";

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $message
        ]);

        session()->flash('success', $message);

        return redirect()->route('inventory.products.show', $this->product);
    }

    public function render()
    {
        return view('inventory::livewire.product-form', [
            'categories' => ProductCategory::all(),
        ])->layout('layouts.app');
    }
}
