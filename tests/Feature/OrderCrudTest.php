<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_create_orders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('orders.store'), [
            'customer_name' => 'Acme Retail',
            'customer_email' => 'buyer@acme.test',
            'status' => 'Pending',
            'total_amount' => 1800,
        ]);

        $response->assertRedirect();

        $order = Order::first();

        $this->assertNotNull($order);
        $this->assertSame('Acme Retail', $order->customer_name);
        $this->assertSame('buyer@acme.test', $order->customer_email);
        $this->assertSame('Pending', $order->status);
        $this->assertSame('1800.00', $order->total_amount);
        $this->assertSame($user->id, $order->user_id);
    }

    public function test_authenticated_users_can_update_orders(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'order_number' => 'SO-TEST-1001',
            'customer_name' => 'Old Customer',
            'customer_email' => 'old@example.com',
            'status' => 'Pending',
            'total_amount' => 250,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('orders.update', $order), [
            'customer_name' => 'Updated Customer',
            'customer_email' => 'updated@example.com',
            'status' => 'Delivered',
            'total_amount' => 999.50,
        ]);

        $response->assertRedirect();

        $order->refresh();

        $this->assertSame('Updated Customer', $order->customer_name);
        $this->assertSame('updated@example.com', $order->customer_email);
        $this->assertSame('Delivered', $order->status);
        $this->assertSame('999.50', $order->total_amount);
    }

    public function test_authenticated_users_can_delete_orders(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'order_number' => 'SO-TEST-2001',
            'customer_name' => 'Disposable Customer',
            'customer_email' => 'delete@example.com',
            'status' => 'Cancelled',
            'total_amount' => 120,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('orders.destroy', $order));

        $response->assertRedirect();
        $this->assertModelMissing($order);
    }
}
