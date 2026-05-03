@extends('layouts.shop', [
    'title' => ($product->meta_title ?: $product->name).' | ElectroHub',
    'description' => $product->meta_description ?: \Illuminate\Support\Str::limit($product->description, 150),
    'image' => $product->display_image,
    'cartCount' => $cartCount,
])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>{{ $product->name }}</h1>
        <a href="{{ route('shop.home') }}" class="btn-secondary">Back to Store</a>
    </div>

    <div class="cart-layout">
        <div class="summary">
            <div class="product-gallery">
                <img src="{{ $product->display_image }}" alt="{{ $product->name }}" class="product-image" id="main-product-image" loading="lazy" decoding="async">
                
                @if($product->images->isNotEmpty())
                    <div class="product-thumbnails" style="display: flex; gap: 10px; margin-top: 15px; overflow-x: auto; padding-bottom: 5px;">
                        <img src="{{ $product->display_image }}" 
                             alt="Thumbnail" 
                             class="thumbnail active" 
                             style="width: 60px; height: 60px; object-fit: cover; cursor: pointer; border: 2px solid var(--primary); border-radius: 4px;"
                             onclick="document.getElementById('main-product-image').src = this.src; document.querySelectorAll('.thumbnail').forEach(t => t.style.borderColor = 'transparent'); this.style.borderColor = 'var(--primary)';">
                        
                        @foreach($product->images as $galleryImage)
                            <img src="{{ Storage::url($galleryImage->image_path) }}" 
                                 alt="Thumbnail" 
                                 class="thumbnail" 
                                 style="width: 60px; height: 60px; object-fit: cover; cursor: pointer; border: 2px solid transparent; border-radius: 4px;"
                                 onclick="document.getElementById('main-product-image').src = this.src; document.querySelectorAll('.thumbnail').forEach(t => t.style.borderColor = 'transparent'); this.style.borderColor = 'var(--primary)';">
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="summary">
            <p class="product-category">{{ $product->category }}</p>
            <p class="product-desc">{{ $product->description }}</p>
            <div class="product-meta">
                <strong>${{ number_format((float) $product->price, 2) }}</strong>
                <span>⭐ {{ number_format((float) $product->rating, 1) }}</span>
            </div>
            <p class="stock-label {{ $product->stock !== null && $product->stock <= 0 ? 'is-out' : '' }}" style="margin-bottom: 15px;">
                <strong>Stock:</strong> 
                @if($product->stock === null)
                    <span style="color: var(--success);">Available</span>
                @elseif($product->stock > 0)
                    <span style="color: var(--success);">{{ $product->stock }} in stock</span>
                @else
                    <span style="color: #ff4d4d; font-weight: bold;">Out of stock</span>
                @endif
            </p>

            <form method="POST" action="{{ route('shop.cart.add', $product->id) }}" class="add-form">
                @csrf
                @if ($product->variants->where('is_active', true)->isNotEmpty())
                    <select name="variant_id" @disabled($product->stock !== null && $product->stock <= 0)>
                        <option value="">Standard</option>
                        @foreach ($product->variants->where('is_active', true) as $variant)
                            <option value="{{ $variant->id }}">
                                {{ $variant->name }} - ${{ number_format($variant->displayPrice($product), 2) }} ({{ $variant->stock }} left)
                            </option>
                        @endforeach
                    </select>
                @endif
                <input type="number" name="quantity" value="1" min="1" max="5" @disabled($product->stock !== null && $product->stock <= 0)>
                <button type="submit" class="btn-primary" @disabled($product->stock !== null && $product->stock <= 0)>
                    {{ ($product->stock !== null && $product->stock <= 0) ? 'Out of Stock' : 'Add to Cart' }}
                </button>
            </form>

            @auth
                <form method="POST" action="{{ route('wishlist.store', $product) }}" class="add-form">
                    @csrf
                    <button type="submit" class="btn-secondary">Add to Wishlist</button>
                </form>
            @endauth
        </div>
    </div>
</section>

<section class="panel reveal">
    <div class="section-head">
        <h2>Reviews</h2>
    </div>

    @auth
        <form method="POST" action="{{ route('products.reviews.store', $product) }}" class="summary">
            @csrf
            <label>Rating (1-5)</label>
            <input type="number" name="rating" min="1" max="5" value="{{ old('rating', $userReview?->rating) }}" required>

            <label>Title</label>
            <input type="text" name="title" value="{{ old('title', $userReview?->title) }}">

            <label>Review</label>
            <textarea name="body" rows="4">{{ old('body', $userReview?->body) }}</textarea>

            <button type="submit" class="btn-primary">Save Review</button>
        </form>
    @else
        <p class="empty">Log in to leave a review.</p>
    @endauth

    <div class="summary">
        @forelse ($reviews as $review)
            <div class="feed-item">
                <strong>{{ $review->user?->name ?? 'User' }}</strong>
                <span>⭐ {{ $review->rating }}</span>
                @if ($review->title)
                    <p><strong>{{ $review->title }}</strong></p>
                @endif
                @if ($review->body)
                    <p>{{ $review->body }}</p>
                @endif
            </div>
        @empty
            <p class="empty">No reviews yet.</p>
        @endforelse
    </div>
</section>

<section class="panel reveal">
    <div class="section-head">
        <h2>Related Products</h2>
    </div>
    <div class="products-grid">
        @forelse ($related as $item)
            <article class="product-card product-card-link reveal" data-href="{{ route('shop.product', $item) }}" tabindex="0" role="link" aria-label="View {{ $item->name }}">
                <div class="product-image-wrap">
                    <img src="{{ $item->display_image }}" alt="{{ $item->name }}" class="product-image" loading="lazy" decoding="async" fetchpriority="low">
                    <span class="badge">{{ $item->badge }}</span>
                </div>
                <div class="product-body">
                    <p class="product-category">{{ $item->category }}</p>
                    <h3>{{ $item->name }}</h3>
                    <p class="product-desc">{{ $item->description }}</p>
                    <div class="product-meta">
                        <strong>${{ number_format((float) $item->price, 2) }}</strong>
                        <span>⭐ {{ number_format((float) $item->rating, 1) }}</span>
                    </div>
                </div>
            </article>
        @empty
            <p class="empty">No related products.</p>
        @endforelse
    </div>
</section>
@endsection
