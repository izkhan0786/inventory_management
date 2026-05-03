<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create required categories
        Category::factory(3)->create();
    }

    public function test_authenticated_users_can_view_products(): void
    {
        $user = User::factory()->create();
        Product::factory(3)->create();

        $response = $this->actingAs($user)->get(route('products.index'));
        $response->assertStatus(200);
    }

    public function test_authenticated_users_can_create_products(): void
    {
        $user = User::factory()->create();
        $category = Category::first();

        $response = $this->actingAs($user)->post(route('products.store'), [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-001',
            'description' => 'A test product',
            'category_id' => $category->id,
            'brand' => 'Test Brand',
            'cost_price' => 50.00,
            'selling_price' => 99.99,
            'initial_stock' => 100,
        ]);

        $response->assertRedirect();

        $product = Product::where('sku', 'TEST-SKU-001')->first();
        $this->assertNotNull($product);
        $this->assertSame('Test Product', $product->name);
        // Verify auto-created default variant
        $this->assertTrue($product->variants()->exists());
    }

    public function test_authenticated_users_can_update_products(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Old Product Name',
            'sku' => 'OLD-SKU',
        ]);

        $response = $this->actingAs($user)->put(route('products.update', $product), [
            'name' => 'Updated Product Name',
            'sku' => 'NEW-SKU',
            'description' => 'Updated description',
            'category_id' => $product->category_id,
            'brand' => 'New Brand',
        ]);

        $response->assertRedirect();

        $product->refresh();
        $this->assertSame('Updated Product Name', $product->name);
        $this->assertSame('NEW-SKU', $product->sku);
    }

    public function test_authenticated_users_can_delete_products(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->delete(route('products.destroy', $product));

        $response->assertRedirect();
        $this->assertModelMissing($product);
    }

    public function test_product_creation_auto_creates_default_variant(): void
    {
        $user = User::factory()->create();
        $category = Category::first();

        $this->actingAs($user)->post(route('products.store'), [
            'name' => 'Auto Variant Product',
            'sku' => 'AUTO-VAR-001',
            'description' => 'Tests auto variant creation',
            'category_id' => $category->id,
            'brand' => 'Test',
            'cost_price' => 25.00,
            'selling_price' => 49.99,
            'initial_stock' => 50,
        ]);

        $product = Product::where('sku', 'AUTO-VAR-001')->first();
        $defaultVariant = $product->variants()->first();

        $this->assertNotNull($defaultVariant);
        $this->assertSame('Default', $defaultVariant->name);
        $this->assertSame($product->id, $defaultVariant->product_id);
    }

    public function test_guest_users_cannot_access_products(): void
    {
        $response = $this->get(route('products.index'));
        $response->assertRedirect('/login');
    }
}
