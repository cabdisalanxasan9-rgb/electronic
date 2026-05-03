@extends('layouts.shop', ['title' => 'Dashboard | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="dashboard-shell reveal">
    <div class="panel dashboard-hero">
        <div>
            <p class="eyebrow">My Space</p>
            <h1>Dashboard</h1>
            <p class="lead">Welcome, {{ auth()->user()->name }}. Manage your profile, orders, and daily shopping here.</p>
        </div>
        <a href="{{ route('shop.home') }}" class="btn-primary">Back to Store</a>
    </div>

    <div class="dashboard-mini-stats">
        <article class="summary dashboard-mini-card">
            <span class="dashboard-mini-label">Account</span>
            <strong>Active</strong>
        </article>
        <article class="summary dashboard-mini-card">
            <span class="dashboard-mini-label">Role</span>
            <strong>{{ auth()->user()->is_admin ? 'Admin' : 'Customer' }}</strong>
        </article>
        <article class="summary dashboard-mini-card">
            <span class="dashboard-mini-label">Checkout</span>
            <strong>Ready</strong>
        </article>
    </div>

    <div class="dashboard-actions">
        <article class="summary dashboard-action-card">
            <h3>Orders</h3>
            <p>Track all your orders and their status.</p>
            <a href="{{ route('orders.index') }}" class="btn-primary full">View Orders</a>
        </article>

        <article class="summary dashboard-action-card">
            <h3>Profile</h3>
            <p>Update your name, email, and password.</p>
            <a href="{{ route('profile.edit') }}" class="btn-secondary full">Profile Edit</a>
        </article>

        <article class="summary dashboard-action-card">
            <h3>Cart</h3>
            <p>Finish checkout or add new items.</p>
            <a href="{{ route('shop.cart') }}" class="btn-secondary full">Tag Cart</a>
        </article>
    </div>
</section>
@endsection
