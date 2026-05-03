<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create required data
        Warehouse::factory(3)->create();
        $product = Product::factory()->create();
        $product->variants()->create([
            'name' => 'Default',
            'sku' => $product->sku . '-default',
            'cost_price' => 10.00,
            'selling_price' => 20.00,
        ]);
    }

    public function test_authenticated_users_can_view_batches(): void
    {
        $user = User::factory()->create();
        Batch::factory(5)->create();

        $response = $this->actingAs($user)->get(route('lots.index'));
        $response->assertStatus(200);
    }

    public function test_authenticated_users_can_create_batches(): void
    {
        $user = User::factory()->create();
        $variant = Product::first()->variants()->first();
        $warehouse = Warehouse::first();

        $response = $this->actingAs($user)->post(route('lots.store'), [
            'batch_number' => 'BATCH-2026-001',
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'manufacturing_date' => '2026-01-15',
            'expiry_date' => '2027-01-15',
            'initial_qty' => 100,
            'current_qty' => 100,
        ]);

        $response->assertRedirect();

        $batch = Batch::where('batch_number', 'BATCH-2026-001')->first();
        $this->assertNotNull($batch);
        $this->assertSame(100, $batch->initial_qty);
        $this->assertSame(100, $batch->current_qty);
        $this->assertSame($warehouse->id, $batch->warehouse_id);
    }

    public function test_authenticated_users_can_update_batches(): void
    {
        $user = User::factory()->create();
        $batch = Batch::factory()->create([
            'batch_number' => 'OLD-BATCH',
            'current_qty' => 100,
        ]);
        $warehouse = Warehouse::find($batch->warehouse_id) ?? Warehouse::first();

        $response = $this->actingAs($user)->put(route('lots.update', $batch), [
            'batch_number' => 'UPDATED-BATCH',
            'warehouse_id' => $warehouse->id,
            'manufacturing_date' => '2026-02-01',
            'expiry_date' => '2027-02-01',
            'current_qty' => 75,
        ]);

        $response->assertRedirect();

        $batch->refresh();
        $this->assertSame('UPDATED-BATCH', $batch->batch_number);
        $this->assertSame(75, $batch->current_qty);
    }

    public function test_authenticated_users_can_delete_batches(): void
    {
        $user = User::factory()->create();
        $batch = Batch::factory()->create();

        $response = $this->actingAs($user)->delete(route('lots.destroy', $batch));

        $response->assertRedirect();
        $this->assertModelMissing($batch);
    }

    public function test_batch_number_must_be_unique(): void
    {
        $user = User::factory()->create();
        Batch::factory()->create(['batch_number' => 'UNIQUE-BATCH']);
        $variant = Product::first()->variants()->first();
        $warehouse = Warehouse::first();

        $response = $this->actingAs($user)->post(route('lots.store'), [
            'batch_number' => 'UNIQUE-BATCH',
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'manufacturing_date' => '2026-01-15',
            'expiry_date' => '2027-01-15',
            'initial_qty' => 50,
            'current_qty' => 50,
        ]);

        $response->assertSessionHasErrors('batch_number');
    }

    public function test_batch_requires_warehouse_id(): void
    {
        $user = User::factory()->create();
        $variant = Product::first()->variants()->first();

        $response = $this->actingAs($user)->post(route('lots.store'), [
            'batch_number' => 'NO-WAREHOUSE-BATCH',
            'variant_id' => $variant->id,
            'warehouse_id' => null,
            'initial_qty' => 50,
            'current_qty' => 50,
        ]);

        $response->assertSessionHasErrors('warehouse_id');
    }

    public function test_guest_users_cannot_access_batches(): void
    {
        $response = $this->get(route('lots.index'));
        $response->assertRedirect('/login');
    }
}
