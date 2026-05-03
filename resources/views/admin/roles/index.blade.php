@extends('layouts.shop', ['title' => 'Roles | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Roles</h1>
        <a href="{{ route('admin.users.index') }}" class="btn-secondary">Manage Users</a>
    </div>

    <div class="admin-stats-grid">
        <article class="summary admin-stat-card">
            <h3>Super Admin</h3>
            <p>Full access: users, roles, products, inventory, orders, returns, reports, coupons, gift cards.</p>
        </article>
        <article class="summary admin-stat-card">
            <h3>Sales Admin</h3>
            <p>Orders, delivery tracking, returns, reports, coupons, gift cards, payment follow-up.</p>
        </article>
        <article class="summary admin-stat-card">
            <h3>Inventory Admin</h3>
            <p>Products, stock, product images, variants, SEO fields, inventory history.</p>
        </article>
        <article class="summary admin-stat-card">
            <h3>Customer</h3>
            <p>Storefront, cart, checkout, wishlist, compare, profile, own orders, returns.</p>
        </article>
    </div>
</section>
@endsection
