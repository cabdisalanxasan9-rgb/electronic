@extends('layouts.shop', ['title' => 'Checkout | ElectroHub', 'cartCount' => $cartCount])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Checkout</h1>
        <a href="{{ route('shop.cart') }}" class="btn-secondary">Back to Cart</a>
    </div>

    <div class="cart-layout">
        <form method="POST" action="{{ route('checkout.store') }}" class="summary">
            @csrf
            <h2>Customer Details</h2>

            <label>Name</label>
            <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" required>

            <label>Email</label>
            <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()->email) }}" required>

            <label>Phone</label>
            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}">

            @if ($addresses->isNotEmpty())
                <label>Saved Address</label>
                <select name="address_id">
                    <option value="">Use custom address</option>
                    @foreach ($addresses as $address)
                        <option value="{{ $address->id }}" @selected(old('address_id') == $address->id)>
                            {{ $address->name }} - {{ $address->line1 }}
                        </option>
                    @endforeach
                </select>
            @endif

            <label>Delivery Address</label>
            <textarea name="shipping_address" rows="4" required>{{ old('shipping_address') }}</textarea>

            <label>Delivery Option</label>
            <select name="shipping_zone" required>
                @foreach (config('shop.shipping_zones', []) as $key => $zone)
                    <option value="{{ $key }}" @selected(old('shipping_zone', 'metro') === $key)>
                        {{ $zone['label'] }} - ${{ number_format((float) $zone['fee'], 2) }} / {{ $zone['eta_days'] }} days
                    </option>
                @endforeach
            </select>

            <label>Payment Method</label>
            <select name="payment_method" required>
                <option value="stripe" @selected(old('payment_method') === 'stripe')>Stripe</option>
                <option value="cod" @selected(old('payment_method') === 'cod')>Cash on Delivery</option>
            </select>

            <label>Notes (optional)</label>
            <textarea name="notes" rows="3">{{ old('notes') }}</textarea>

            <button type="submit" class="btn-primary full">Place Order</button>
        </form>

        <aside class="summary">
            <h2>Your Items</h2>
            @foreach ($items as $item)
                <p>
                    <span>{{ $item['product']->name }}{{ $item['variant'] ? ' / '.$item['variant']->name : '' }} x{{ $item['quantity'] }}</span>
                    <strong>${{ number_format($item['lineTotal'], 2) }}</strong>
                </p>
            @endforeach
            <p><span>Subtotal</span><strong>${{ number_format($subTotal, 2) }}</strong></p>
            @if (! empty($coupon))
                <p><span>Discount ({{ $coupon->code }})</span><strong>- ${{ number_format($discount, 2) }}</strong></p>
            @endif
            <p><span>Tax</span><strong>${{ number_format($tax, 2) }}</strong></p>
            <p><span>Shipping</span><strong>${{ number_format($shipping, 2) }}</strong></p>
            <p class="total"><span>Total</span><strong>${{ number_format($grandTotal, 2) }}</strong></p>
        </aside>
    </div>
</section>
@endsection
