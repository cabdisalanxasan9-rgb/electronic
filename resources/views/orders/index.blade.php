@extends('layouts.shop', ['title' => 'Orders | ElectroHub', 'cartCount' => $cartCount])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Your Orders</h1>
        <a href="{{ route('shop.home') }}" class="btn-secondary">Store</a>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>${{ number_format((float) $order->grand_total, 2) }}</td>
                        <td>{{ strtoupper($order->payment_status) }} / {{ strtoupper($order->payment_method) }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td><a class="btn-secondary" href="{{ route('orders.show', $order) }}">Details</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">You have not placed any orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}
</section>
@endsection
