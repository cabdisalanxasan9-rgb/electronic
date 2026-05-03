@extends('layouts.shop', ['title' => 'Wishlist | ElectroHub', 'cartCount' => $cartCount])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Wishlist</h1>
        <a href="{{ route('shop.home') }}" class="btn-secondary">Back to Store</a>
    </div>

    @if ($items->isEmpty())
        <p class="empty">Your wishlist is empty.</p>
    @else
        <div class="products-grid">
            @foreach ($items as $item)
                @if ($item->product)
                    <article class="product-card reveal">
                        <div class="product-image-wrap">
                            <img src="{{ $item->product->display_image }}" alt="{{ $item->product->name }}" class="product-image" loading="lazy" decoding="async" fetchpriority="low">
                            <span class="badge">{{ $item->product->badge }}</span>
                        </div>
                        <div class="product-body">
                            <p class="product-category">{{ $item->product->category }}</p>
                            <h3>{{ $item->product->name }}</h3>
                            <p class="product-desc">{{ $item->product->description }}</p>
                            <div class="product-meta">
                                <strong>${{ number_format((float) $item->product->price, 2) }}</strong>
                                <span>⭐ {{ number_format((float) $item->product->rating, 1) }}</span>
                            </div>
                            <div class="add-form">
                                <a href="{{ route('shop.product', $item->product) }}" class="btn-secondary">View</a>
                                <form method="POST" action="{{ route('wishlist.destroy', $item->product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Remove</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    @endif
</section>
@endsection
