<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class BatchFactory extends Factory
{
    protected $model = Batch::class;

    public function definition(): array
    {
        return [
            'batch_number' => fake()->unique()->bothify('BATCH-####-??'),
            'variant_id' => ProductVariant::factory(),
            'warehouse_id' => Warehouse::factory(),
            'manufacturing_date' => fake()->dateTimeBetween('-6 months'),
            'expiry_date' => fake()->dateTimeBetween('+1 month', '+12 months'),
            'initial_qty' => fake()->randomNumber(2),
            'current_qty' => fake()->randomNumber(2),
        ];
    }
}
