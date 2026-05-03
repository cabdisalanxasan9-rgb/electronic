@extends('layouts.shop', ['title' => 'Admin Dashboard | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Admin Dashboard</h1>
        <div class="admin-actions">
            @if (auth()->user()->canManageProducts())
                <a href="{{ route('admin.products.index') }}" class="btn-secondary">Products</a>
                <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Categories</a>
                <a href="{{ route('admin.brands.index') }}" class="btn-secondary">Brands</a>
                <a href="{{ route('admin.pages.index') }}" class="btn-secondary">Pages</a>
                <a href="{{ route('admin.contacts.index') }}" class="btn-secondary">Contacts</a>
                <a href="{{ route('admin.inventory.index') }}" class="btn-secondary">Inventory History</a>
            @endif
            @if (auth()->user()->canManageOrders())
                <a href="{{ route('admin.orders.index') }}" class="btn-secondary">Orders</a>
            @endif
            @if (auth()->user()->canManageUsers())
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Users</a>
                <a href="{{ route('admin.roles.index') }}" class="btn-secondary">Roles</a>
                <a href="{{ route('admin.security.index') }}" class="btn-secondary">Security</a>
            @endif
            @if (auth()->user()->canManageOrders())
                <a href="{{ route('admin.coupons.index') }}" class="btn-secondary">Coupons</a>
                <a href="{{ route('admin.gift-cards.index') }}" class="btn-secondary">Gift Cards</a>
                <a href="{{ route('admin.returns.index') }}" class="btn-secondary">Returns</a>
                <a href="{{ route('admin.reports.index') }}" class="btn-secondary">Reports</a>
            @endif
        </div>
    </div>

    <div class="admin-stats-grid">
        <article class="summary admin-stat-card"><h3>Sales Today</h3><p class="total"><strong>${{ number_format($stats['todaySales'], 2) }}</strong></p></article>
        <article class="summary admin-stat-card"><h3>Pending Orders</h3><p class="total"><strong>{{ $stats['pendingOrders'] }}</strong></p></article>
        <article class="summary admin-stat-card"><h3>Total Orders</h3><p class="total"><strong>{{ $stats['totalOrders'] }}</strong></p></article>
        <article class="summary admin-stat-card"><h3>Total Products</h3><p class="total"><strong>{{ $stats['totalProducts'] }}</strong></p></article>
        <article class="summary admin-stat-card"><h3>Total Users</h3><p class="total"><strong>{{ $stats['totalUsers'] }}</strong></p></article>
        <article class="summary admin-stat-card"><h3>Low Stock</h3><p class="total"><strong>{{ $stats['lowStock'] }}</strong></p></article>
        <article class="summary admin-stat-card"><h3>Pending Returns</h3><p class="total"><strong>{{ $stats['pendingReturns'] }}</strong></p></article>
    </div>

    <div class="cart-layout">
        <div class="summary">
            <h2>Top Products</h2>
            @forelse ($topProducts as $item)
                <p><span>{{ $item->product_name }}</span><strong>{{ $item->total_qty }} sold</strong></p>
            @empty
                <p>No sales data yet.</p>
            @endforelse
        </div>

        <div class="summary">
            <h2>Notifications</h2>
            @forelse ($notifications as $notification)
                <p class="feed-item">
                    <strong>{{ data_get($notification->data, 'message') }}</strong>
                    <span class="feed-item-meta">{{ $notification->created_at->diffForHumans() }}</span>
                </p>
            @empty
                <p>No notifications.</p>
            @endforelse
        </div>
    </div>

    <div class="cart-layout section-space-top">
        <div class="summary">
            <h2>Low Stock Products</h2>
            @forelse ($lowStockProducts as $product)
                <p><span>{{ $product->name }}</span><strong>{{ $product->stock }} left</strong></p>
            @empty
                <p>No low stock items.</p>
            @endforelse
        </div>

        <div class="summary">
            <h2>Recent Returns</h2>
            @forelse ($recentReturns as $return)
                <p class="feed-item">
                    <strong>{{ $return->order?->order_number ?? 'Order' }}</strong>
                    <span class="feed-item-meta">{{ ucfirst($return->status) }}</span>
                </p>
            @empty
                <p>No returns yet.</p>
            @endforelse
        </div>
    </div>

    <div class="summary section-space-top">
        <h2>Audit Logs</h2>
        @forelse ($latestAudits as $audit)
            <p class="feed-item">
                <strong>{{ $audit->action }}</strong>
                <span class="feed-item-meta">by {{ $audit->user?->name ?? 'System' }} at {{ $audit->created_at->format('Y-m-d H:i') }}</span>
            </p>
        @empty
            <p>No audit logs.</p>
        @endforelse
    </div>
</section>
@endsection
