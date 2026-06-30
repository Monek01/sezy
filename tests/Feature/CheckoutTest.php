<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_product_to_cart(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'published', 'stock' => 10]);

        $response = $this->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_full_checkout_flow_creates_order_and_decrements_stock(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'published', 'stock' => 5, 'price' => 10000]);

        $this->actingAs($user);

        $this->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2]);

        $this->post(route('checkout.shipping.store'), [
            'full_name' => 'Jean Test',
            'email' => $user->email,
            'phone' => '+22900000000',
            'delivery_method' => 'home_delivery',
            'city' => 'Cotonou',
            'address_line' => '123 Rue Test',
        ])->assertRedirect(route('checkout.payment'));

        $response = $this->post(route('checkout.payment.store'), [
            'payment_method' => 'mtn_momo',
            'payer_phone' => '+22900000000',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertEquals(3, $product->fresh()->stock); // 5 - 2 = 3
        $this->assertDatabaseCount('cart_items', 0); // panier vidé après commande
    }

    public function test_admin_routes_are_protected(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
