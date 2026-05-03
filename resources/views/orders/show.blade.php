@extends('layouts.shop', ['title' => 'Order Detail | ElectroHub', 'cartCount' => $cartCount])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Order {{ $order->order_number }}</h1>
        <a href="{{ route('orders.index') }}" class="btn-secondary">All Orders</a>
    </div>

    <div class="cart-layout">
        <div class="summary">
            <h2>Order Info</h2>
            <p><span>Status</span><strong>{{ ucfirst($order->status) }}</strong></p>
            <p><span>Payment</span><strong>{{ strtoupper($order->payment_status) }} / {{ strtoupper($order->payment_method) }}</strong></p>
            @if ($order->payment_failure_reason)
                <p><span>Payment Note</span><strong>{{ $order->payment_failure_reason }}</strong></p>
            @endif
            @if ($order->coupon_code)
                <p><span>Coupon</span><strong>{{ $order->coupon_code }}</strong></p>
            @endif
            <p><span>Email</span><strong>{{ $order->customer_email }}</strong></p>
            <p><span>Phone</span><strong>{{ $order->customer_phone ?: '-' }}</strong></p>
            <p><span>Address</span><strong>{{ $order->shipping_address }}</strong></p>
            <p><span>Courier</span><strong>{{ $order->courier_name ?: '-' }}</strong></p>
            <p><span>Tracking</span><strong>{{ $order->tracking_number ?: '-' }}</strong></p>
            <p><span>ETA</span><strong>{{ $order->estimated_delivery_at?->format('Y-m-d') ?? '-' }}</strong></p>
            <p><span>Subtotal</span><strong>${{ number_format((float) $order->sub_total, 2) }}</strong></p>
            <p><span>Discount</span><strong>- ${{ number_format((float) $order->discount_amount, 2) }}</strong></p>
            <p><span>Tax</span><strong>${{ number_format((float) $order->tax_amount, 2) }}</strong></p>
            <p class="total"><span>Total</span><strong>${{ number_format((float) $order->grand_total, 2) }}</strong></p>
        </div>

        <div class="summary">
            <h2>Items</h2>
            @foreach ($order->items as $item)
                <p>
                    <span>{{ $item->product_name }} x{{ $item->quantity }}</span>
                    <strong>${{ number_format((float) $item->line_total, 2) }}</strong>
                </p>
            @endforeach
        </div>
    </div>

    <div class="summary section-space-top">
        <h2>Order Tracking</h2>
        <div class="tracking-steps">
            @foreach ($trackingSteps as $step)
                <div class="tracking-step {{ $step['is_complete'] ? 'is-complete' : '' }}">
                    <span class="tracking-dot"></span>
                    <strong>{{ $step['label'] }}</strong>
                    <small>{{ str_replace('_', ' ', ucfirst($step['status'])) }}</small>
                </div>
            @endforeach
        </div>

        <div class="tracking-history">
            @forelse ($order->statusLogs as $log)
                <p class="feed-item">
                    <strong>{{ str_replace('_', ' ', ucfirst((string) $log->new_status)) }}</strong>
                    <span class="feed-item-meta">{{ $log->created_at?->format('Y-m-d H:i') }} by {{ $log->user?->name ?? 'System' }}</span>
                    @if ($log->note)
                        <span class="feed-item-note">{{ $log->note }}</span>
                    @endif
                </p>
            @empty
                <p class="empty">Tracking updates will appear here when the order changes.</p>
            @endforelse
        </div>
    </div>

    <div class="summary section-space-top">
        <h2>Payment Activity</h2>
        @forelse ($order->paymentEvents as $event)
            <p class="feed-item">
                <strong>{{ ucfirst(str_replace('_', ' ', $event->event_type)) }} / {{ strtoupper($event->status) }}</strong>
                <span class="feed-item-meta">{{ $event->created_at?->format('Y-m-d H:i') }} via {{ strtoupper($event->provider) }}</span>
            </p>
        @empty
            <p class="empty">No payment events recorded yet.</p>
        @endforelse
    </div>

    <div class="summary section-space-top">
        <h2>Return Request</h2>
        @if ($order->returns->isNotEmpty())
            @foreach ($order->returns as $return)
                <p class="feed-item">
                    <strong>{{ ucfirst($return->status) }}</strong>
                    <span class="feed-item-meta">{{ $return->created_at?->format('Y-m-d') }}</span>
                    <span class="feed-item-note">{{ $return->reason }}</span>
                </p>
            @endforeach
        @else
            @if ($canReturn)
                <form method="POST" action="{{ route('orders.returns.store', $order) }}">
                    @csrf
                    <label>Reason</label>
                    <textarea name="reason" rows="3" required></textarea>
                    <p class="empty">Return window: {{ $returnWindowDays }} days from order date.</p>
                    <button class="btn-secondary" type="submit">Submit Return</button>
                </form>
            @else
                <p class="empty">This order is not eligible for return.</p>
            @endif
        @endif
    </div>
</section>
@endsection
