<?php

namespace App\Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Inventory\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        if (Warehouse::count() === 0) {
            Warehouse::create([
                'name' => 'Entrepôt Principal',
                'address' => 'Siège Social',
                'is_default' => true,
            ]);
            
            Warehouse::create([
                'name' => 'Boutique Centre',
                'address' => 'Centre Ville',
                'is_default' => false,
            ]);
        }
    }
}
