<?php

namespace App\Console\Commands;

use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Models\StockLevel;
use App\Notifications\StockAlert; // Assuming notification class exists or I should create it
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class StockCheckAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for products below minimum stock allowed and notify';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking stock alerts...');

        // Strategy: Get all products with track_stock = true
        // Check global stock or per warehouse?
        // Usually min_stock_alert is per product (global) or per warehouse?
        // Product model has min_stock_alert.
        // If we sum all warehouses stock vs min_stock_alert?
        // Or check default warehouse?
        // Let's assume global stock for simplicity unless warehouse specified.

        $products = Product::where('track_stock', true)
            ->where('is_active', true)
            ->get();

        $alerts = [];

        foreach ($products as $product) {
            // Calculate total stock
            $totalStock = $product->stockLevels()->sum('quantity');

            if ($totalStock <= $product->min_stock_alert) {
                $alerts[] = [
                    'product' => $product,
                    'current' => $totalStock,
                    'min' => $product->min_stock_alert,
                ];

                $this->warn("Product {$product->reference} ({$product->name}) is low on stock: {$totalStock} <= {$product->min_stock_alert}");
            }
        }

        if (count($alerts) > 0) {
            // Send notification to users with permission 'stock.create' or 'stock.view' or specific role
            // Finding users to notify (e.g. Admins, Stock Managers)

            // For now, logging.
            // Notification::send($users, new StockAlert($alerts));
            $this->info("Found " . count($alerts) . " products in alert.");
        } else {
            $this->info("No alerts found.");
        }
    }
}
