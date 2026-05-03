@extends('layouts.shop', ['title' => 'ElectroHub | Cart', 'cartCount' => $cartCount])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Your Cart</h1>
        <a href="{{ route('shop.home') }}" class="btn-secondary">Continue Shopping</a>
    </div>

    @if ($items->isEmpty())
        <div class="empty-cart-state" style="text-align: center; padding: 60px 20px;">
            <i class="bi bi-cart-x" style="font-size: 4rem; color: var(--muted); opacity: 0.5;"></i>
            <p class="empty" style="font-size: 1.2rem; margin-top: 20px;">Your cart is empty. Pick something you like and add it to your cart.</p>
            <a href="{{ route('shop.home') }}" class="btn-primary" style="margin-top: 20px;">Start Shopping</a>
        </div>
    @else
        <div class="cart-layout">
            <div class="cart-items">
                @foreach ($items as $item)
                    <article class="cart-item reveal">
                        <img src="{{ $item['product']->display_image }}" alt="{{ $item['product']->name }}" loading="lazy" decoding="async">
                        <div class="item-details">
                            <p class="product-category">{{ $item['product']->category }}</p>
                            <h3>{{ $item['product']->name }}</h3>
                            @if ($item['variant'])
                                <p class="variant-label" style="font-size: 0.9rem; color: var(--muted);">Variant: {{ $item['variant']->name }}</p>
                            @endif
                            <div style="display: flex; align-items: center; gap: 20px; margin-top: 10px;">
                                <p style="margin:0;">Qty: <strong>{{ $item['quantity'] }}</strong></p>
                                <strong>${{ number_format($item['lineTotal'], 2) }}</strong>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('shop.cart.remove', urlencode($item['key'] ?? $item['product']->id)) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger" title="Remove Item">
                                <i class="bi bi-trash"></i>
                                <span class="hide-mobile">Remove</span>
                            </button>
                        </form>
                    </article>
                @endforeach
                
                <div style="margin-top: 20px;">
                    <form method="POST" action="{{ route('shop.cart.clear') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary" style="border-color: var(--danger); color: var(--danger);">
                            <i class="bi bi-trash-fill" style="margin-right: 8px;"></i> Clear Entire Cart
                        </button>
                    </form>
                </div>
            </div>

            <aside class="cart-summary-card reveal">
                <h2>Order Summary</h2>
                
                <div class="summary-row">
                    <span><i class="bi bi-tag"></i> Subtotal</span>
                    <strong>${{ number_format($subTotal, 2) }}</strong>
                </div>
                
                @if (! empty($coupon))
                    <div class="summary-row" style="color: var(--accent);">
                        <span><i class="bi bi-percent"></i> Discount ({{ $coupon->code }})</span>
                        <strong>- ${{ number_format($discount, 2) }}</strong>
                    </div>
                @endif
                
                <div class="summary-row">
                    <span><i class="bi bi-receipt"></i> Tax ({{ (config('shop.tax_rate', 0.05) * 100) }}%)</span>
                    <strong>${{ number_format($tax, 2) }}</strong>
                </div>
                
                <div class="summary-row">
                    <span><i class="bi bi-truck"></i> Shipping</span>
                    <strong>${{ number_format($shipping, 2) }}</strong>
                </div>
                
                <div class="summary-row total">
                    <span><i class="bi bi-wallet2"></i> Total</span>
                    <strong>${{ number_format($grandTotal, 2) }}</strong>
                </div>

                <div class="coupon-section">
                    @auth
                        <form method="POST" action="{{ route('cart.coupon.apply') }}">
                            @csrf
                            <label style="margin-top:0;">Have a Coupon?</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="text" name="code" value="{{ old('code') }}" placeholder="CODE" style="flex: 1;">
                                <button type="submit" class="btn-secondary">Apply</button>
                            </div>
                        </form>

                        @if (! empty($coupon))
                            <form method="POST" action="{{ route('cart.coupon.remove') }}" style="margin-top:8px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger full" style="font-size: 0.85rem; padding: 6px;">Remove Coupon</button>
                            </form>
                        @endif
                    @else
                        <p style="font-size: 0.85rem; color: var(--muted); text-align: center;">Log in to use coupons</p>
                    @endauth
                </div>

                <div style="margin-top: 24px;">
                    @auth
                        <a href="{{ route('checkout.show') }}" class="btn-primary full" style="font-size: 1.1rem; height: 52px;">
                            Checkout <i class="bi bi-arrow-right" style="margin-left: 8px;"></i>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary full" style="font-size: 1.1rem; height: 52px;">
                            Log in to Checkout
                        </a>
                    @endauth
                    
                    <p style="font-size: 0.75rem; color: var(--muted); text-align: center; margin-top: 15px;">
                        <i class="bi bi-shield-check"></i> Secure Checkout Guaranteed
                    </p>
                </div>
            </aside>
        </div>
    @endif
</section>
@endsection
