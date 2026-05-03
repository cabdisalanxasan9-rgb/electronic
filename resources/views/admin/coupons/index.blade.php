@extends('layouts.shop', ['title' => 'Admin Coupons | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Admin: Coupons</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Dashboard</a>
    </div>

    <form method="POST" action="{{ route('admin.coupons.store') }}" class="summary">
        @csrf
        <h2>Create Coupon</h2>
        <label>Code</label>
        <input type="text" name="code" required>
        <label>Type</label>
        <select name="type">
            <option value="fixed">Fixed</option>
        </select>
        <label>Amount</label>
        <input type="number" step="0.01" name="amount" required>
        <label>Minimum Order</label>
        <input type="number" step="0.01" name="minimum_order" value="0">
        <label>Usage Limit</label>
        <input type="number" name="usage_limit">
        <label>Starts At</label>
        <input type="datetime-local" name="starts_at">
        <label>Ends At</label>
        <input type="datetime-local" name="ends_at">
        <label class="product-check-row">
            <input type="checkbox" name="is_active" value="1" checked>
            Active
        </label>
        <button type="submit" class="btn-primary">Save Coupon</button>
    </form>

    <div class="summary section-space-top">
        <h2>Coupons</h2>
        @forelse ($coupons as $coupon)
            <div class="feed-item">
                <strong>{{ $coupon->code }}</strong>
                <span>{{ strtoupper($coupon->type) }} - ${{ number_format((float) $coupon->amount, 2) }}</span>
                <span>Min: ${{ number_format((float) $coupon->minimum_order, 2) }}</span>
                <span>Used: {{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}</span>
                <span>{{ $coupon->is_active ? 'Active' : 'Disabled' }}</span>
                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn-danger" type="submit">Delete</button>
                </form>
            </div>
        @empty
            <p class="empty">No coupons yet.</p>
        @endforelse
    </div>
</section>
@endsection
