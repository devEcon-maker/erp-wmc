<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Models\StockLevel;
use App\Modules\Inventory\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class StockService
{
    public function getAvailableStock(int $productId, int $warehouseId): float
    {
        $level = $this->getStockLevel($productId, $warehouseId);
        return $level->quantity - $level->reserved_quantity;
    }

    public function addStock(int $productId, int $warehouseId, float $quantity, string $notes = null, Model $reference = null)
    {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $notes, $reference) {
            $this->createMovement($productId, $warehouseId, 'in', $quantity, $notes, $reference);

            $level = $this->getStockLevel($productId, $warehouseId);
            $level->increment('quantity', $quantity);

            return $level;
        });
    }

    public function removeStock(int $productId, int $warehouseId, float $quantity, string $notes = null, Model $reference = null)
    {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $notes, $reference) {
            $level = $this->getStockLevel($productId, $warehouseId);

            // Validation: stock cannot be negative (unless allowed setting)
            if ($level->quantity < $quantity) {
                // ideally throw exception or handle error
            }

            $this->createMovement($productId, $warehouseId, 'out', -$quantity, $notes, $reference);
            $level->decrement('quantity', $quantity);

            return $level;
        });
    }

    public function reserve(int $productId, int $warehouseId, float $quantity, Model $reference = null)
    {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $reference) {
            $level = $this->getStockLevel($productId, $warehouseId);
            $level->increment('reserved_quantity', $quantity);
            return $level;
        });
    }

    public function release(int $productId, int $warehouseId, float $quantity)
    {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity) {
            $level = $this->getStockLevel($productId, $warehouseId);
            $level->decrement('reserved_quantity', $quantity);
            return $level;
        });
    }

    public function transfer(int $productId, int $fromWarehouseId, int $toWarehouseId, float $quantity, string $notes = null)
    {
        return DB::transaction(function () use ($productId, $fromWarehouseId, $toWarehouseId, $quantity, $notes) {
            // Check source stock
            $fromLevel = $this->getStockLevel($productId, $fromWarehouseId);
            if ($fromLevel->quantity < $quantity) {
                // throw exception
            }

            // Create movement
            $movement = new StockMovement([
                'product_id' => $productId,
                'warehouse_id' => $toWarehouseId, // Destination
                'from_warehouse_id' => $fromWarehouseId, // Source
                'type' => 'transfer',
                'quantity' => $quantity,
                'date' => now(),
                'notes' => $notes,
                'created_by' => auth()->id() ?? 1,
            ]);
            $movement->save();

            // Update levels
            $fromLevel->decrement('quantity', $quantity);

            $toLevel = $this->getStockLevel($productId, $toWarehouseId);
            $toLevel->increment('quantity', $quantity);

            return $movement;
        });
    }

    public function adjust(int $productId, int $warehouseId, float $newQuantity, string $notes = null)
    {
        return DB::transaction(function () use ($productId, $warehouseId, $newQuantity, $notes) {
            $level = $this->getStockLevel($productId, $warehouseId);
            $diff = $newQuantity - $level->quantity;

            if ($diff == 0)
                return $level;

            $this->createMovement($productId, $warehouseId, 'adjustment', $diff, $notes);

            $level->quantity = $newQuantity;
            $level->save();

            return $level;
        });
    }

    protected function getStockLevel($productId, $warehouseId)
    {
        return StockLevel::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['quantity' => 0, 'reserved_quantity' => 0]
        );
    }

    protected function createMovement($productId, $warehouseId, $type, $quantity, $notes, $reference = null)
    {
        $movement = new StockMovement([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'type' => $type,
            'quantity' => $quantity,
            'date' => now(),
            'notes' => $notes,
            'created_by' => auth()->id() ?? 1,
        ]);

        if ($reference) {
            $movement->reference()->associate($reference);
        }

        $movement->save();
        return $movement;
    }
}
