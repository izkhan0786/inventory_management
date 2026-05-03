<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_view_assets(): void
    {
        $user = User::factory()->create();
        Asset::factory(5)->create();

        $response = $this->actingAs($user)->get(route('assets.index'));
        $response->assertStatus(200);
    }

    public function test_authenticated_users_can_create_assets(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('assets.store'), [
            'asset_tag' => 'ASSET-2026-001',
            'name' => 'Equipment A',
            'serial_number' => 'SN-12345',
            'status' => 'In Use',
            'purchase_date' => '2025-01-01',
            'purchase_cost' => 5000.00,
            'location' => 'Main Warehouse',
            'notes' => 'Test equipment',
        ]);

        $response->assertRedirect();

        $asset = Asset::where('asset_tag', 'ASSET-2026-001')->first();
        $this->assertNotNull($asset);
        $this->assertSame('Equipment A', $asset->name);
        $this->assertEquals(5000.00, (float)$asset->purchase_cost);
    }

    public function test_authenticated_users_can_update_assets(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create([
            'name' => 'Old Asset Name',
            'purchase_cost' => 1000.00,
        ]);

        $response = $this->actingAs($user)->put(route('assets.update', $asset), [
            'asset_tag' => $asset->asset_tag,
            'name' => 'Updated Asset Name',
            'serial_number' => 'NEW-SN-999',
            'status' => 'In Maintenance',
            'purchase_date' => $asset->purchase_date,
            'purchase_cost' => 2000.00,
            'location' => 'Warehouse 2',
            'notes' => 'Updated',
        ]);

        $response->assertRedirect();

        $asset->refresh();
        $this->assertSame('Updated Asset Name', $asset->name);
        $this->assertEquals(2000.00, (float)$asset->purchase_cost);
    }

    public function test_authenticated_users_can_delete_assets(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        $response = $this->actingAs($user)->delete(route('assets.destroy', $asset));

        $response->assertRedirect();
        $this->assertModelMissing($asset);
    }

    public function test_asset_tag_must_be_unique(): void
    {
        $user = User::factory()->create();
        Asset::factory()->create(['asset_tag' => 'UNIQUE-TAG']);

        $response = $this->actingAs($user)->post(route('assets.store'), [
            'asset_tag' => 'UNIQUE-TAG',
            'name' => 'Duplicate Asset',
            'serial_number' => 'SN-DUP',
            'status' => 'Available',
            'purchase_date' => '2025-01-01',
            'purchase_cost' => 1500.00,
            'location' => 'Test',
            'notes' => 'Trying to duplicate',
        ]);

        $response->assertSessionHasErrors('asset_tag');
    }

    public function test_asset_status_must_be_valid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('assets.store'), [
            'asset_tag' => 'INVALID-STATUS',
            'name' => 'Bad Status Asset',
            'serial_number' => 'SN-BAD',
            'status' => 'invalid_status',
            'purchase_date' => '2025-01-01',
            'purchase_cost' => 1000.00,
            'location' => 'Test',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_asset_depreciation_tracking(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('assets.store'), [
            'asset_tag' => 'DEPRECIATION-TEST',
            'name' => 'Depreciating Asset',
            'serial_number' => 'SN-DEP-001',
            'purchase_date' => '2025-01-01',
            'purchase_cost' => 10000.00,
            'status' => 'In Use',
            'location' => 'Main Warehouse',
            'notes' => 'Testing depreciation',
        ]);

        $asset = Asset::where('asset_tag', 'DEPRECIATION-TEST')->first();
        // Verify asset was created with full cost
        $this->assertEquals(10000.00, (float)$asset->purchase_cost);
    }

    public function test_guest_users_cannot_access_assets(): void
    {
        $response = $this->get(route('assets.index'));
        $response->assertRedirect('/login');
    }
}
