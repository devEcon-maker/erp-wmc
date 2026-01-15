<?php

namespace App\Modules\Inventory\Livewire\Purchases;

use App\Modules\Inventory\Models\Product;
use App\Modules\CRM\Models\Contact;
use App\Modules\Inventory\Services\PurchaseOrderService;
use App\Modules\Inventory\Models\PurchaseOrder;
use Livewire\Component;

class ReorderSuggestions extends Component
{
    public $selectedProducts = [];

    public function getSuggestionsProperty()
    {
        return Product::where('is_active', true)
            ->where('track_stock', true)
            // ->whereRaw('stock_quantity <= min_stock_alert') // Need aggregate stock? 
            // Let's use simpler query assuming global stock check or iterating.
            // Since we don't have global 'stock_quantity' on product, we use relation.
            ->get()
            ->filter(function ($product) {
                $totalStock = $product->stockLevels()->sum('quantity');
                return $totalStock <= $product->min_stock_alert;
            });
    }

    public function createPurchaseOrder()
    {
        // Group by supplier?
        // Logic: For selected products, create POs.
        // Assuming user selects products to reorder.
        // Need supplier logic. Product doesn't strictly have a 'default supplier' in schema provided in prompt 1.7.
        // Prompt 1.7: Product (type, reference, ...). No supplier_id.
        // We might need to ask user for supplier.

        // MVP: Just redirect to PO Create with selected products?
        // Or create Draft POs if we knew supplier?

        // Let's redirect to create with query params or session?
        // Or open modal to select supplier?

        // For now, let's say we just show the list. Since we can't easily auto-group without supplier info on product.

        $this->dispatch('notify', message: 'Fonctionnalité en cours de développement (Manque lien Produit-Fournisseur)', type: 'warning');
    }

    public function render()
    {
        return view('livewire.inventory.purchases.reorder-suggestions', [
            'suggestions' => $this->suggestions,
        ])->layout('layouts.app');
    }
}
