<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryMovementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Warehouse::factory(2)->create();
        $product = Product::factory()->create();
        $product->variants()->create([
            'name' => 'Default',
            'sku' => $product->sku . '-default',
            'cost_price' => 10.00,
            'selling_price' => 20.00,
            'stock_qty' => 100,
        ]);
    }

    public function test_authenticated_users_can_view_inventory_movements(): void
    {
        $user = User::factory()->create();
        StockMovement::factory(8)->create();

        $response = $this->actingAs($user)->get(route('inventory.index'));
        $response->assertStatus(200);
    }

    public function test_authenticated_users_can_record_stock_movement(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::first();
        $warehouse = Warehouse::first();

        $response = $this->actingAs($user)->post(route('inventory.movement'), [
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'OUT',
            'quantity' => 25,
            'reference_no' => 'INV-TEST-001',
            'notes' => 'Stock rebalancing',
        ]);

        $response->assertRedirect();

        $movement = StockMovement::where('variant_id', $variant->id)->first();
        $this->assertNotNull($movement);
        $this->assertSame('OUT', $movement->type);
        $this->assertSame(25, $movement->quantity);
    }

    public function test_authenticated_users_can_update_stock_movement(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::first();
        $warehouse = Warehouse::first();
        $movement = StockMovement::factory()->create([
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
        ]);

        $response = $this->actingAs($user)->put(route('inventory.update', $movement->id), [
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'OUT',
            'quantity' => 5,
            'reference_no' => 'INV-EDIT',
            'notes' => 'Corrected movement',
        ]);

        $response->assertRedirect();

        $movement->refresh();
        $this->assertSame('OUT', $movement->type);
        $this->assertSame(5, $movement->quantity);
        $this->assertSame('INV-EDIT', $movement->reference_no);
    }

    public function test_authenticated_users_can_delete_stock_movement(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::first();
        $warehouse = Warehouse::first();
        $movement = StockMovement::factory()->create([
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'IN',
            'quantity' => 20,
            'balance_after' => 20,
        ]);

        $response = $this->actingAs($user)->delete(route('inventory.destroy', $movement->id));

        $response->assertRedirect();
        $this->assertNull(StockMovement::find($movement->id));
    }

    public function test_inventory_movement_updates_variant_quantity(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::first();
        $warehouse = Warehouse::first();

        // Record an OUT movement for 30 units
        $this->actingAs($user)->post(route('inventory.movement'), [
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'OUT',
            'quantity' => 30,
            'notes' => 'Consumed in production',
        ]);

        $variant->refresh();
        // Stock was 100, OUT 30, should be 70
        $this->assertSame(70, $variant->stock_qty);
    }

    public function test_inventory_summary_fetches_correctly(): void
    {
        $user = User::factory()->create();
        ProductVariant::factory(3)->create(['stock_qty' => 100]);
        ProductVariant::factory(1)->create(['stock_qty' => 50]);
        ProductVariant::factory(1)->create(['stock_qty' => 25]);

        $response = $this->actingAs($user)->get(route('inventory.index'));
        $response->assertStatus(200);
    }

    public function test_guest_users_cannot_access_inventory(): void
    {
        $response = $this->get(route('inventory.index'));
        $response->assertRedirect('/login');
    }

    public function test_guest_users_cannot_record_movements(): void
    {
        $response = $this->post(route('inventory.movement'), [
            'variant_id' => 1,
            'warehouse_id' => 1,
            'type' => 'IN',
            'quantity' => 10,
        ]);
        
        $response->assertRedirect('/login');
    }
}
