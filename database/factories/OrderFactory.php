<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => fake()->unique()->bothify('SO-????-##'),
            'customer_name' => fake()->company(),
            'customer_email' => fake()->companyEmail(),
            'status' => fake()->randomElement(['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled']),
            'total_amount' => fake()->randomFloat(2, 100, 5000),
            'user_id' => User::factory(),
        ];
    }
}
