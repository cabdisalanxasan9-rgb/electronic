@extends('layouts.shop', ['title' => 'Admin Order Detail', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Admin Order: {{ $order->order_number }}</h1>
        <div class="admin-actions">
            <a href="{{ route('admin.orders.print', $order) }}" class="btn-secondary" target="_blank">Print Invoice</a>
            <a href="{{ route('admin.orders.index') }}" class="btn-secondary">Back</a>
            <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Are you sure you want to delete this order?')">
                @csrf
                @method('DELETE')
                <button class="btn-danger" type="submit">Delete Order</button>
            </form>
        </div>
    </div>

    <div class="cart-layout">
        <div class="summary">
            <h2>Customer</h2>
            <p><span>Name</span><strong>{{ $order->customer_name }}</strong></p>
            <p><span>Email</span><strong>{{ $order->customer_email }}</strong></p>
            <p><span>Phone</span><strong>{{ $order->customer_phone ?: '-' }}</strong></p>
            <p><span>Address</span><strong>{{ $order->shipping_address }}</strong></p>
            <p><span>Delivery</span><strong>{{ $order->shipping_zone ?: '-' }}</strong></p>
            <p><span>Courier</span><strong>{{ $order->courier_name ?: '-' }}</strong></p>
            <p><span>Tracking</span><strong>{{ $order->tracking_number ?: '-' }}</strong></p>
            <p><span>ETA</span><strong>{{ $order->estimated_delivery_at?->format('Y-m-d') ?? '-' }}</strong></p>
            @if ($order->coupon_code)
                <p><span>Coupon</span><strong>{{ $order->coupon_code }}</strong></p>
            @endif
            @if ($order->payment_failure_reason)
                <p><span>Payment Note</span><strong>{{ $order->payment_failure_reason }}</strong></p>
            @endif
            <p><span>Subtotal</span><strong>${{ number_format((float) $order->sub_total, 2) }}</strong></p>
            <p><span>Discount</span><strong>- ${{ number_format((float) $order->discount_amount, 2) }}</strong></p>
            <p><span>Tax</span><strong>${{ number_format((float) $order->tax_amount, 2) }}</strong></p>
            <p class="total"><span>Total</span><strong>${{ number_format((float) $order->grand_total, 2) }}</strong></p>
        </div>

        <div class="summary">
            <h2>Update Status</h2>
            <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                @csrf
                @method('PATCH')

                <label>Status</label>
                <select name="status">
                    @foreach (['pending_payment', 'processing', 'shipped', 'completed', 'cancelled', 'delivered'] as $status)
                        <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>

                <label>Payment Status</label>
                <select name="payment_status">
                    @foreach (['pending', 'paid', 'failed', 'refunded'] as $status)
                        <option value="{{ $status }}" @selected($order->payment_status === $status)>{{ strtoupper($status) }}</option>
                    @endforeach
                </select>

                <label>Note</label>
                <textarea name="note" rows="3" placeholder="Status update note..."></textarea>

                <label>Courier</label>
                <input type="text" name="courier_name" value="{{ old('courier_name', $order->courier_name) }}">

                <label>Tracking Number</label>
                <input type="text" name="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}">

                <label>Estimated Delivery</label>
                <input type="date" name="estimated_delivery_at" value="{{ old('estimated_delivery_at', $order->estimated_delivery_at?->format('Y-m-d')) }}">

                <button class="btn-primary section-space-top" type="submit">Save Status</button>
            </form>

            <div class="admin-actions section-space-top">
                <form method="POST" action="{{ route('admin.orders.delivered', $order) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn-secondary" type="submit">Mark Delivered</button>
                </form>
                <form method="POST" action="{{ route('admin.orders.refund', $order) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn-danger" type="submit">Refund</button>
                </form>
            </div>
        </div>
    </div>

    <div class="summary section-space-top">
        <h2>Items</h2>
        @foreach ($order->items as $item)
            <p>
                <span>{{ $item->product_name }}{{ $item->variant_name ? ' / '.$item->variant_name : '' }} x{{ $item->quantity }}</span>
                <strong>${{ number_format((float) $item->line_total, 2) }}</strong>
            </p>
        @endforeach
    </div>

    <div class="summary section-space-top">
        <h2>Status Timeline</h2>
        @forelse ($order->statusLogs as $log)
            <p class="feed-item">
                <strong>{{ ucfirst(str_replace('_', ' ', $log->new_status)) }}</strong>
                <span class="feed-item-meta">{{ $log->created_at->format('Y-m-d H:i') }} by {{ $log->user?->name ?? 'System' }}</span>
                @if ($log->note)
                    <span class="feed-item-note">{{ $log->note }}</span>
                @endif
            </p>
        @empty
            <p>No timeline entries yet.</p>
        @endforelse
    </div>

    <div class="summary section-space-top">
        <h2>Payment Events</h2>
        @forelse ($order->paymentEvents as $event)
            <p class="feed-item">
                <strong>{{ ucfirst(str_replace('_', ' ', $event->event_type)) }} / {{ strtoupper($event->status) }}</strong>
                <span class="feed-item-meta">{{ $event->created_at?->format('Y-m-d H:i') }} via {{ strtoupper($event->provider) }}</span>
                @if ($event->reference)
                    <span class="feed-item-note">{{ $event->reference }}</span>
                @endif
            </p>
        @empty
            <p>No payment events yet.</p>
        @endforelse
    </div>

    <div class="summary section-space-top">
        <h2>Returns</h2>
        @forelse ($order->returns as $return)
            <p class="feed-item">
                <strong>{{ ucfirst($return->status) }}</strong>
                <span class="feed-item-meta">{{ $return->created_at?->format('Y-m-d') }} by {{ $return->user?->name ?? 'User' }}</span>
                <span class="feed-item-note">{{ $return->reason }}</span>
            </p>
        @empty
            <p>No return requests.</p>
        @endforelse
    </div>
</section>
@endsection
