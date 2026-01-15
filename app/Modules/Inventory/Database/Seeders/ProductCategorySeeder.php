<?php

namespace App\Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Inventory\Models\ProductCategory;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Géolocalisation',
            'Télécommunication et Solutions Digitales',
            'Sécurité Digitale',
            'Applications & Logiciels',
            'Multimédia',
            'Matériels de Bureau',
            'Autres Produits',
        ];

        foreach ($categories as $name) {
            ProductCategory::firstOrCreate(['name' => $name]);
        }

        $this->command->info('Catégories de produits créées avec succès !');
    }
}
