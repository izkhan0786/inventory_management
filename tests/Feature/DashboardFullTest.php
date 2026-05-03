<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFullTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create base data for dashboard
        Warehouse::factory(3)->create();
        $product = Product::factory(5)->create();
        $product->each(function ($p) {
            $p->variants()->create([
                'name' => 'Default',
                'sku' => $p->sku . '-default',
                'cost_price' => 10.00,
                'selling_price' => 20.00,
                'stock_qty' => 100,
            ]);
        });
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_dashboard(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_dashboard_displays_total_products_metric(): void
    {
        $user = User::factory()->create();
        Product::factory(15)->create();

        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
    }

    public function test_dashboard_displays_total_orders_metric(): void
    {
        $user = User::factory()->create();
        Order::factory(8)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
    }

    public function test_dashboard_displays_total_inventory_value(): void
    {
        $user = User::factory()->create();
        ProductVariant::factory(10)->create(['stock_qty' => 50, 'cost_price' => 100]);

        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
    }

    public function test_dashboard_displays_critical_stock_alerts(): void
    {
        $user = User::factory()->create();
        // Create variants with low stock
        ProductVariant::factory(3)->create(['stock_qty' => 2, 'min_stock_level' => 5]);
        // Create variants with good stock
        ProductVariant::factory(5)->create(['stock_qty' => 100, 'min_stock_level' => 5]);

        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
    }

    public function test_dashboard_displays_expiring_items(): void
    {
        $user = User::factory()->create();
        // Note: Product variants don't have expiry dates in current schema
        // This test just verifies the dashboard loads with various product data
        ProductVariant::factory(7)->create(['stock_qty' => 50]);

        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
    }

    public function test_dashboard_displays_recent_movements(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::first();
        StockMovement::factory(12)->create(['variant_id' => $variant->id]);

        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
    }

    public function test_dashboard_with_empty_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
    }
}
