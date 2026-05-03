<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

uses(DatabaseTransactions::class);

it('requires image upload when admin creates product', function () {
    Gate::before(fn () => true);

    $admin = User::factory()->create(['is_admin' => true]);
    $startCount = Product::query()->count();

    $response = test()->withoutMiddleware(PreventRequestForgery::class)->from(route('admin.products.create'))->actingAs($admin)->post(route('admin.products.store'), [
        'id' => 'P-TEST-001',
        'name' => 'Test Product',
        'category' => 'Phones',
        'price' => 199.99,
        'stock' => 10,
        'rating' => 4.5,
        'description' => 'Test description',
    ]);

    $response->assertSessionHasErrors(['image_file']);
    expect(Product::query()->count())->toBe($startCount);
});

it('updates and removes product image through admin form', function () {
    Gate::before(fn () => true);

    Storage::fake('public');

    $admin = User::factory()->create(['is_admin' => true]);

    $tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WmY2x8AAAAASUVORK5CYII=');

    $firstPath = storage_path('app/public/test-image-1.png');
    if (! is_dir(dirname($firstPath))) {
        mkdir(dirname($firstPath), 0777, true);
    }
    file_put_contents($firstPath, $tinyPng);

    $secondPath = storage_path('app/public/test-image-2.png');
    file_put_contents($secondPath, $tinyPng);

    $createResponse = test()->withoutMiddleware(PreventRequestForgery::class)->actingAs($admin)->post(route('admin.products.store'), [
        'id' => 'P-TEST-002',
        'name' => 'Camera X',
        'category' => 'Cameras',
        'price' => 499.00,
        'stock' => 5,
        'rating' => 4.2,
        'description' => 'Camera description',
        'image_file' => new UploadedFile($firstPath, 'camera-1.png', 'image/png', null, true),
    ]);

    $createResponse->assertRedirect(route('admin.products.index'));

    $product = Product::query()->findOrFail('P-TEST-002');
    expect($product->image_path)->not()->toBeNull();

    $newImage = new UploadedFile($secondPath, 'camera-2.png', 'image/png', null, true);

    $updateResponse = test()->withoutMiddleware(PreventRequestForgery::class)->actingAs($admin)->patch(route('admin.products.update', $product), [
        'id' => $product->id,
        'name' => $product->name,
        'category' => $product->category,
        'price' => $product->price,
        'stock' => $product->stock,
        'rating' => $product->rating,
        'description' => $product->description,
        'image_file' => $newImage,
    ]);

    $updateResponse->assertRedirect(route('admin.products.index'));

    $product->refresh();
    expect($product->image_path)->not()->toBeNull();

    $removeResponse = test()->withoutMiddleware(PreventRequestForgery::class)->actingAs($admin)->patch(route('admin.products.update', $product), [
        'id' => $product->id,
        'name' => $product->name,
        'category' => $product->category,
        'price' => $product->price,
        'stock' => $product->stock,
        'rating' => $product->rating,
        'description' => $product->description,
        'remove_image' => 1,
    ]);

    $removeResponse->assertRedirect(route('admin.products.index'));

    $product->refresh();
    expect($product->image_path)->toBeNull();
});
