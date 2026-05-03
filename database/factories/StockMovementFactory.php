<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'variant_id' => ProductVariant::factory(),
            'warehouse_id' => Warehouse::factory(),
            'type' => fake()->randomElement(['IN', 'OUT', 'ADJUSTMENT', 'TRANSFER']),
            'quantity' => fake()->numberBetween(1, 100),
            'balance_after' => fake()->numberBetween(0, 500),
            'reference_no' => fake()->bothify('REF-####-??'),
            'user_id' => User::factory(),
            'notes' => fake()->sentence(),
        ];
    }
}
