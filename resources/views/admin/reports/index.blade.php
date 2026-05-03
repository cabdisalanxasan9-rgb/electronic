@extends('layouts.shop', ['title' => 'Admin Reports | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Reports</h1>
        <div class="admin-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Dashboard</a>
            <a href="{{ route('admin.reports.orders.csv') }}" class="btn-secondary">Export CSV</a>
            <a href="{{ route('admin.reports.orders.pdf') }}" class="btn-secondary">Export PDF</a>
        </div>
    </div>

    <div class="admin-stats-grid">
        <article class="summary admin-stat-card"><h3>Sales Today</h3><p class="total"><strong>${{ number_format($stats['todaySales'], 2) }}</strong></p></article>
        <article class="summary admin-stat-card"><h3>Monthly Revenue</h3><p class="total"><strong>${{ number_format($stats['monthlyRevenue'], 2) }}</strong></p></article>
        <article class="summary admin-stat-card"><h3>Paid Orders</h3><p class="total"><strong>{{ $stats['paidOrders'] }}</strong></p></article>
        <article class="summary admin-stat-card"><h3>Low Stock</h3><p class="total"><strong>{{ $stats['lowStock'] }}</strong></p></article>
    </div>

    <div class="cart-layout">
        <div class="summary">
            <h2>Last 14 Days</h2>
            @php $maxSales = max(1, (float) $dailySales->max('total')); @endphp
            @forelse ($dailySales as $day)
                <div class="chart-row">
                    <span>{{ \Illuminate\Support\Carbon::parse($day->day)->format('M d') }}</span>
                    <div class="chart-track"><span style="width: {{ max(4, ((float) $day->total / $maxSales) * 100) }}%"></span></div>
                    <strong>${{ number_format((float) $day->total, 2) }}</strong>
                </div>
            @empty
                <p>No sales data yet.</p>
            @endforelse
        </div>

        <div class="summary">
            <h2>Top Products</h2>
            @forelse ($topProducts as $product)
                <p>
                    <span>{{ $product->product_name }}</span>
                    <strong>{{ $product->total_qty }} sold / ${{ number_format((float) $product->total_sales, 2) }}</strong>
                </p>
            @empty
                <p>No product sales yet.</p>
            @endforelse
        </div>
    </div>

    <div class="summary section-space-top">
        <h2>Low Stock Alerts</h2>
        @forelse ($lowStockProducts as $product)
            <p>
                <span>{{ $product->name }}</span>
                <strong>{{ $product->stock }} left</strong>
            </p>
        @empty
            <p>No low stock items.</p>
        @endforelse
    </div>
</section>
@endsection
