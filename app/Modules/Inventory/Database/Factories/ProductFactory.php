<?php

namespace App\Modules\Inventory\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\Inventory\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 10, 1000);
        return [
            'type' => 'product',
            'reference' => strtoupper($this->faker->unique()->bothify('REF-####')),
            'name' => $this->faker->words(3, true),
            'purchase_price' => $price,
            'selling_price' => $price * 1.5,
            'tax_rate' => 20.00,
            'is_active' => true,
            'track_stock' => true,
        ];
    }
}
