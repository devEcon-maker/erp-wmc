<?php

namespace App\Modules\HR\Database\Seeders;

use App\Modules\HR\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Transport',
                'max_amount' => 200.00,
                'requires_receipt' => true,
            ],
            [
                'name' => 'Repas',
                'max_amount' => 25.00,
                'requires_receipt' => true,
            ],
            [
                'name' => 'Hébergement',
                'max_amount' => 150.00,
                'requires_receipt' => true,
            ],
            [
                'name' => 'Fournitures',
                'max_amount' => 100.00,
                'requires_receipt' => true,
            ],
            [
                'name' => 'Autre',
                'max_amount' => null,
                'requires_receipt' => false,
            ],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
