@extends('layouts.shop', ['title' => 'ElectroHub | Electronics Store', 'cartCount' => $cartCount])

@section('content')
<section class="hero reveal">
    <div>
        <p class="eyebrow">No. 1 Electronics Store</p>
        <h1>Modern gear designed for an amazing digital life.</h1>
        <p class="lead">Find phones, laptops, audio, TVs, and accessories with great quality, fair pricing, and fast service.</p>
        <div class="hero-actions">
            <a href="#products" class="btn-primary">Start Shopping</a>
            <a href="{{ route('shop.cart') }}" class="btn-secondary">View Cart</a>
        </div>

        <div class="hero-quickstats">
            <span>1200+ products</span>
            <span>Secure payments</span>
            <span>Fast support</span>
        </div>
    </div>
    <div class="hero-card">
        <h3>Store Highlights</h3>
        <p class="metric">Free Delivery 24h</p>
        <p class="metric">Warranty up to 2 years</p>
        <p class="metric">Secure payments</p>
    </div>
</section>

<section id="products" class="panel reveal">
    <p class="active-category-label">
        Category: <strong>{{ $activeCategory === 'all' ? 'All' : ($categories->firstWhere('slug', $activeCategory)?->name ?? $activeCategory) }}</strong>
    </p>

    <div class="category-chips">
        <a
            href="{{ route('shop.home', array_filter(['category' => 'all', 'q' => $search, 'sort' => $sort, 'price_min' => $filters['price_min'] ?? null, 'price_max' => $filters['price_max'] ?? null, 'rating_min' => $filters['rating_min'] ?? null, 'in_stock' => ($filters['in_stock'] ?? false) ? 1 : null], fn ($value) => $value !== null && $value !== '')) }}"
            class="chip {{ $activeCategory === 'all' ? 'is-active' : '' }}"
        >
            All
        </a>
        @foreach ($categories as $category)
            <a
                href="{{ route('shop.home', array_filter(['category' => $category->slug, 'q' => $search, 'sort' => $sort, 'price_min' => $filters['price_min'] ?? null, 'price_max' => $filters['price_max'] ?? null, 'rating_min' => $filters['rating_min'] ?? null, 'in_stock' => ($filters['in_stock'] ?? false) ? 1 : null], fn ($value) => $value !== null && $value !== '')) }}"
                class="chip {{ $activeCategory === $category->slug ? 'is-active' : '' }}"
            >
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('shop.home') }}" class="filters">
        <input type="text" name="q" value="{{ $search }}" placeholder="Search products...">

        <select name="category" onchange="this.form.submit()">
            <option value="all" @selected($activeCategory === 'all')>All Categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->slug }}" @selected($activeCategory === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>

        <input type="number" name="price_min" value="{{ $filters['price_min'] ?? '' }}" placeholder="Min price" step="0.01" min="0">
        <input type="number" name="price_max" value="{{ $filters['price_max'] ?? '' }}" placeholder="Max price" step="0.01" min="0">
        <input type="number" name="rating_min" value="{{ $filters['rating_min'] ?? '' }}" placeholder="Min rating" step="0.1" min="0" max="5">

        <label class="product-check-row">
            <input type="checkbox" name="in_stock" value="1" @checked($filters['in_stock'] ?? false)>
            In stock only
        </label>

        <select name="sort">
            <option value="featured" @selected($sort === 'featured')>Featured</option>
            <option value="price_low" @selected($sort === 'price_low')>Price: Low to High</option>
            <option value="price_high" @selected($sort === 'price_high')>Price: High to Low</option>
            <option value="rating" @selected($sort === 'rating')>Top Rated</option>
        </select>

        <button type="submit" class="btn-secondary">Filter</button>
    </form>

    <div class="products-grid">
        @forelse ($products as $product)
            <article class="product-card product-card-link reveal" data-href="{{ route('shop.product', $product) }}" tabindex="0" role="link" aria-label="View {{ $product->name }}">
                <div class="product-image-wrap">
                    <img src="{{ $product->display_image }}" alt="{{ $product->name }}" class="product-image" loading="lazy" decoding="async" fetchpriority="low">
                    <span class="badge">{{ $product->badge }}</span>
                </div>
                <div class="product-body">
                    <p class="product-category">{{ $product->category }}</p>
                    <h3>
                        <a href="{{ route('shop.product', $product) }}">{{ $product->name }}</a>
                    </h3>
                    <p class="product-desc">{{ $product->description }}</p>
                    <div class="product-meta">
                        <strong>${{ number_format((float) $product->price, 2) }}</strong>
                        <span>⭐ {{ number_format((float) $product->rating, 1) }}</span>
                    </div>
                    <p class="stock-label {{ $product->stock !== null && $product->stock <= 0 ? 'is-out' : '' }}">
                        {{ $product->stock === null ? 'Stock available' : ($product->stock > 0 ? $product->stock.' in stock' : 'Out of stock') }}
                    </p>
                    <form method="POST" action="{{ route('shop.cart.add', $product->id) }}" class="add-form">
                        @csrf
                        <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock === null ? 5 : max(1, min(5, (int) $product->stock)) }}" @disabled($product->stock !== null && $product->stock <= 0)>
                        <button type="submit" class="btn-primary" @disabled($product->stock !== null && $product->stock <= 0)>Add to Cart</button>
                    </form>
                    @auth
                        <form method="POST" action="{{ route('wishlist.store', $product) }}" class="add-form">
                            @csrf
                            <button type="submit" class="btn-secondary">Add to Wishlist</button>
                        </form>
                    @endauth
                </div>
            </article>
        @empty
            <p class="empty">No products found. Try a different search or category.</p>
        @endforelse
    </div>
</section>

@if (! empty($recentProducts) && $recentProducts->isNotEmpty())
    <section class="panel reveal">
        <div class="section-head">
            <h2>Recently Viewed</h2>
        </div>
        <div class="products-grid">
            @foreach ($recentProducts as $product)
                <article class="product-card product-card-link reveal" data-href="{{ route('shop.product', $product) }}" tabindex="0" role="link" aria-label="View {{ $product->name }}">
                    <div class="product-image-wrap">
                        <img src="{{ $product->display_image }}" alt="{{ $product->name }}" class="product-image" loading="lazy" decoding="async" fetchpriority="low">
                        <span class="badge">{{ $product->badge }}</span>
                    </div>
                    <div class="product-body">
                        <p class="product-category">{{ $product->category }}</p>
                        <h3>
                            <a href="{{ route('shop.product', $product) }}">{{ $product->name }}</a>
                        </h3>
                        <div class="product-meta">
                            <strong>${{ number_format((float) $product->price, 2) }}</strong>
                            <span>⭐ {{ number_format((float) $product->rating, 1) }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
@endsection
