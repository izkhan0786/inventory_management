<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'asset_tag' => fake()->unique()->bothify('ASSET-####-??'),
            'name' => fake()->word() . ' ' . fake()->word(),
            'serial_number' => fake()->unique()->bothify('SN-####-???'),
            'purchase_date' => fake()->dateTimeBetween('-5 years'),
            'purchase_cost' => fake()->randomFloat(2, 500, 50000),
            'status' => fake()->randomElement(['In Use', 'In Maintenance', 'Available', 'Retired', 'Broken']),
            'location' => fake()->city(),
            'user_id' => null,
            'notes' => fake()->sentence(),
        ];
    }
}
