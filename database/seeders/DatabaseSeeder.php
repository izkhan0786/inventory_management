<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@nexus.com',
            'password' => bcrypt('password'),
        ]);

        // Create Categories
        $categories = \App\Models\Category::factory(5)->create();

        // Create Warehouses
        $warehouses = \App\Models\Warehouse::factory(3)->create();

        // Create Products and Variants
        foreach ($categories as $category) {
            \App\Models\Product::factory(4)
                ->for($category)
                ->has(\App\Models\ProductVariant::factory(2), 'variants')
                ->create()
                ->each(function ($product) use ($warehouses) {
                    foreach ($product->variants as $variant) {
                        // Create some stock movements for each variant
                        \App\Models\StockMovement::factory(5)->create([
                            'variant_id' => $variant->id,
                            'warehouse_id' => $warehouses->random()->id,
                            'user_id' => 1,
                        ]);
                        
                        // Update stock_qty based on movements
                        $qty = \App\Models\StockMovement::where('variant_id', $variant->id)->sum('quantity');
                        $variant->update(['stock_qty' => $qty]);
                    }
                });
        }

        // Create some Orders
        \App\Models\Order::factory(10)->create();

        // Create some Assets
        \App\Models\Asset::factory(5)->create();
    }
}
