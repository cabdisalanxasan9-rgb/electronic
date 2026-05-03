@extends('layouts.shop', ['title' => 'Admin Orders | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Admin: Orders</h1>
        <div class="admin-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Dashboard</a>
            <a href="{{ route('admin.products.index') }}" class="btn-secondary">Products</a>
            <a href="{{ route('admin.orders.create') }}" class="btn-primary">Create Order</a>
            <a href="{{ route('admin.reports.orders.csv') }}" class="btn-secondary">Export CSV</a>
            <a href="{{ route('admin.reports.orders.pdf') }}" class="btn-secondary">Export PDF</a>
        </div>
    </div>

    <form method="GET" class="filters filters-admin-orders">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search order # or customer...">

        <select name="status">
            <option value="all" @selected($status === 'all')>All Status</option>
            @foreach (['pending_payment', 'processing', 'shipped', 'completed', 'cancelled', 'delivered'] as $item)
                <option value="{{ $item }}" @selected($status === $item)>{{ ucfirst(str_replace('_', ' ', $item)) }}</option>
            @endforeach
        </select>

        <select name="payment_status">
            <option value="all" @selected(($paymentStatus ?? 'all') === 'all')>All Payment</option>
            @foreach (['pending', 'paid', 'failed', 'refunded'] as $payment)
                <option value="{{ $payment }}" @selected(($paymentStatus ?? 'all') === $payment)>{{ strtoupper($payment) }}</option>
            @endforeach
        </select>

        <button class="btn-secondary" type="submit">Filter</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>User</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->user?->email }}</td>
                        <td>${{ number_format((float) $order->grand_total, 2) }}</td>
                        <td>{{ strtoupper($order->payment_status) }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="btn-secondary">Open</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}
</section>
@endsection
