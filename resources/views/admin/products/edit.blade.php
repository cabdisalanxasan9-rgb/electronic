@extends('layouts.shop', ['title' => 'Edit Product | Admin', 'cartCount' => 0])

@section('content')
<section class="edit-shell reveal">
    <div class="edit-hero">
        <p class="eyebrow">Admin Studio</p>
        <h1>Edit Product</h1>
        <p class="edit-lead">Manage product details and imagery here quickly and cleanly.</p>
    </div>

    <div class="edit-layout">
        <div class="panel">
            <div class="section-head">
                <h2 class="edit-section-title">Product Details</h2>
                <a href="{{ route('admin.products.index') }}" class="btn-secondary">Back</a>
            </div>

            @include('admin.products.form', [
                'action' => route('admin.products.update', $product),
                'method' => 'PATCH',
                'product' => $product,
                'saveLabel' => 'Update Product',
            ])
        </div>

        <aside class="edit-meta panel">
            <h3>Quick Info</h3>
            <p><strong>ID:</strong> {{ $product->id }}</p>
            <p><strong>Category:</strong> {{ $product->category }}</p>
            <p><strong>Price:</strong> ${{ number_format((float) $product->price, 2) }}</p>
            <p><strong>Rating:</strong> {{ number_format((float) $product->rating, 1) }}/5</p>
            <p><strong>Last Update:</strong> {{ $product->updated_at?->diffForHumans() }}</p>
        </aside>
    </div>
</section>
@endsection
