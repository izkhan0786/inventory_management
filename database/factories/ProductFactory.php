<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word() . ' ' . fake()->word(),
            'sku' => fake()->unique()->bothify('???-###'),
            'description' => fake()->sentence(),
            'category_id' => Category::factory(),
            'brand' => fake()->randomElement(['Acme', 'Best', 'Premium', 'Value', 'Select']),
            'image_path' => null,
            'has_variants' => false,
        ];
    }
}
