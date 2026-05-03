<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Mockery;

uses(DatabaseTransactions::class);

function makeProduct(array $overrides = []): Product
{
    $data = array_merge([
        'id' => 'P-'.uniqid(),
        'name' => 'Test Product',
        'category' => 'General',
        'price' => 25.00,
        'rating' => 4.1,
        'featured' => false,
        'badge' => null,
        'stock' => 5,
        'description' => 'Test description',
        'image' => 'https://example.test/image.png',
        'image_path' => null,
    ], $overrides);

    return Product::query()->create($data);
}

it('decrements stock for COD checkout', function () {
    Mail::fake();

    $user = User::factory()->create();
    $product = makeProduct(['stock' => 6]);

    $response = test()
        ->withoutMiddleware(PreventRequestForgery::class)
        ->actingAs($user)
        ->withSession(['cart' => [$product->id => 2]])
        ->post(route('checkout.store'), [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => '252611000000',
            'shipping_address' => '123 Test Street',
            'payment_method' => 'cod',
        ]);

    $order = Order::query()->latest()->first();
    expect($order)->not()->toBeNull();
    $response->assertRedirect(route('orders.show', $order));

    $product->refresh();
    expect($product->stock)->toBe(4);
});

it('does not decrement stock before Stripe payment', function () {
    Mail::fake();

    config(['services.stripe.secret' => 'sk_test_123']);

    $mock = Mockery::mock('alias:Stripe\\Checkout\\Session');
    $mock->shouldReceive('create')
        ->once()
        ->andReturn((object) ['url' => 'https://example.test/stripe']);

    $user = User::factory()->create();
    $product = makeProduct(['stock' => 7]);

    $response = test()
        ->withoutMiddleware(PreventRequestForgery::class)
        ->actingAs($user)
        ->withSession(['cart' => [$product->id => 3]])
        ->post(route('checkout.store'), [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => '252611000000',
            'shipping_address' => '123 Test Street',
            'payment_method' => 'stripe',
        ]);

    $response->assertRedirect('https://example.test/stripe');

    $product->refresh();
    expect($product->stock)->toBe(7);
});

it('decrements stock when admin creates an order', function () {
    Gate::before(fn () => true);

    $admin = User::factory()->create(['is_admin' => true]);
    $product = makeProduct(['stock' => 5]);

    $response = test()
        ->withoutMiddleware(PreventRequestForgery::class)
        ->actingAs($admin)
        ->post(route('admin.orders.store'), [
            'user_id' => $admin->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'shipping_amount' => 0,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => 'processing',
            'customer_name' => $admin->name,
            'customer_email' => $admin->email,
            'customer_phone' => '252611000000',
            'shipping_address' => 'Admin Address',
        ]);

    $order = Order::query()->latest()->first();
    expect($order)->not()->toBeNull();
    $response->assertRedirect(route('admin.orders.show', $order));

    $product->refresh();
    expect($product->stock)->toBe(3);
});
