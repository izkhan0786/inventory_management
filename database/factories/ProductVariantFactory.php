<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->randomElement(['Standard', 'Premium', 'Economy', 'Deluxe', 'Basic']),
            'sku' => fake()->unique()->bothify('VAR-###-???'),
            'cost_price' => fake()->randomFloat(2, 1, 200),
            'selling_price' => fake()->randomFloat(2, 10, 500),
            'stock_qty' => fake()->randomNumber(2),
            'min_stock_level' => 5,
            'barcode' => fake()->unique()->ean13(),
        ];
    }
}
