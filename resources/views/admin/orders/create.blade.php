@extends('layouts.shop', ['title' => 'Create Admin Order | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal order-create-shell">
    <div class="section-head">
        <h1>Create Admin Order</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn-secondary">Back to Orders</a>
    </div>

    <form method="POST" action="{{ route('admin.orders.store') }}" class="summary order-create-form" data-draft-form="admin-order-create">
        @csrf
        <div class="order-create-grid">
            <div class="order-step-card">
                <h3>1) Choose User & Product</h3>

                <label for="user_id">User</label>
                <select id="user_id" name="user_id" required>
                    <option value="">Select user</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')<p class="field-error">{{ $message }}</p>@enderror

                <label for="product_id">Product</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Select product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                            {{ $product->name }} - ${{ number_format((float) $product->price, 2) }}
                        </option>
                    @endforeach
                </select>
                @error('product_id')<p class="field-error">{{ $message }}</p>@enderror

                <label for="quantity">Quantity</label>
                <input id="quantity" type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="20" required>
                @error('quantity')<p class="field-error">{{ $message }}</p>@enderror

                <label for="shipping_amount">Shipping Amount</label>
                <input id="shipping_amount" type="number" step="0.01" name="shipping_amount" value="{{ old('shipping_amount', 0) }}" min="0">
                @error('shipping_amount')<p class="field-error">{{ $message }}</p>@enderror

                <label>Delivery Zone</label>
                <select name="shipping_zone">
                    <option value="">Manual</option>
                    @foreach (config('shop.shipping_zones') as $key => $zone)
                        <option value="{{ $key }}" @selected(old('shipping_zone') === $key)>{{ $zone['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="order-step-card">
                <h3>2) Payment & Status</h3>

                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="stripe" @selected(old('payment_method') === 'stripe')>Stripe</option>
                    <option value="cod" @selected(old('payment_method') === 'cod')>Cash on Delivery</option>
                </select>
                @error('payment_method')<p class="field-error">{{ $message }}</p>@enderror

                <label for="payment_status">Payment Status</label>
                <select id="payment_status" name="payment_status" required>
                    @foreach (['pending', 'paid', 'failed', 'refunded'] as $status)
                        <option value="{{ $status }}" @selected(old('payment_status', 'pending') === $status)>{{ strtoupper($status) }}</option>
                    @endforeach
                </select>
                @error('payment_status')<p class="field-error">{{ $message }}</p>@enderror

                <label for="status">Order Status</label>
                <select id="status" name="status" required>
                    @foreach (['pending_payment', 'processing', 'shipped', 'completed', 'cancelled', 'delivered'] as $status)
                        <option value="{{ $status }}" @selected(old('status', 'processing') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                @error('status')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="order-step-card order-step-card-full">
                <h3>3) Customer Info</h3>

                <div class="order-customer-grid">
                    <div>
                        <label for="customer_name">Customer Name</label>
                        <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}" required>
                        @error('customer_name')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="customer_email">Customer Email</label>
                        <input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email') }}" required>
                        @error('customer_email')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <label for="customer_phone">Customer Phone</label>
                <input id="customer_phone" type="text" name="customer_phone" value="{{ old('customer_phone') }}">
                @error('customer_phone')<p class="field-error">{{ $message }}</p>@enderror

                <label for="shipping_address">Shipping Address</label>
                <textarea id="shipping_address" name="shipping_address" rows="3" required>{{ old('shipping_address') }}</textarea>
                @error('shipping_address')<p class="field-error">{{ $message }}</p>@enderror

                <div class="order-customer-grid">
                    <div>
                        <label>Courier</label>
                        <input type="text" name="courier_name" value="{{ old('courier_name') }}">
                    </div>
                    <div>
                        <label>Tracking Number</label>
                        <input type="text" name="tracking_number" value="{{ old('tracking_number') }}">
                    </div>
                </div>

                <label>Estimated Delivery</label>
                <input type="date" name="estimated_delivery_at" value="{{ old('estimated_delivery_at') }}">

                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                @error('notes')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <button type="submit" class="btn-primary full">Create Order</button>
    </form>
</section>
@endsection
