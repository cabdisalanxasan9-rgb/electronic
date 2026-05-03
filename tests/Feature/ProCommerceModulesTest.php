<?php

use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Mail;

uses(DatabaseTransactions::class);

it('limits admin areas by role', function () {
    $inventoryAdmin = User::factory()->create([
        'is_admin' => true,
        'role' => User::ROLE_INVENTORY_ADMIN,
    ]);

    test()->actingAs($inventoryAdmin)->get(route('admin.products.index'))->assertOk();
    test()->actingAs($inventoryAdmin)->get(route('admin.orders.index'))->assertForbidden();

    $salesAdmin = User::factory()->create([
        'is_admin' => true,
        'role' => User::ROLE_SALES_ADMIN,
    ]);

    test()->actingAs($salesAdmin)->get(route('admin.orders.index'))->assertOk();
    test()->actingAs($salesAdmin)->get(route('admin.products.index'))->assertForbidden();
});

it('checks out a selected variant and records inventory history', function () {
    Mail::fake();

    $user = User::factory()->create();
    $product = Product::query()->create([
        'id' => 'P-VARIANT-'.uniqid(),
        'name' => 'Variant Phone',
        'category' => 'Phones',
        'price' => 100,
        'rating' => 4.5,
        'featured' => false,
        'badge' => null,
        'stock' => 99,
        'description' => 'Variant capable phone',
        'image' => 'https://example.test/phone.png',
    ]);
    $variant = ProductVariant::query()->create([
        'product_id' => $product->id,
        'name' => 'Black / 256GB',
        'sku' => 'BLACK-256',
        'price_delta' => 20,
        'stock' => 3,
        'is_active' => true,
    ]);

    $response = test()
        ->withoutMiddleware(PreventRequestForgery::class)
        ->actingAs($user)
        ->withSession(['cart' => [$product->id.':'.$variant->id => 2]])
        ->post(route('checkout.store'), [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'shipping_address' => '123 Test Street',
            'shipping_zone' => 'pickup',
            'payment_method' => 'cod',
        ]);

    $order = Order::query()->latest()->firstOrFail();
    $response->assertRedirect(route('orders.show', $order));

    $variant->refresh();
    expect($variant->stock)->toBe(1)
        ->and($order->items()->first()->variant_name)->toBe('Black / 256GB')
        ->and(InventoryAdjustment::query()->where('product_variant_id', $variant->id)->exists())->toBeTrue();
});
