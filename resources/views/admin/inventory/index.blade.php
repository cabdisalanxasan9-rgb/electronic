@extends('layouts.shop', ['title' => 'Inventory History | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Inventory History</h1>
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">Products</a>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>Old</th>
                    <th>New</th>
                    <th>Delta</th>
                    <th>Reason</th>
                    <th>User</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($adjustments as $adjustment)
                    <tr>
                        <td>{{ $adjustment->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $adjustment->product?->name ?? $adjustment->product_id }}</td>
                        <td>{{ $adjustment->variant?->name ?? '-' }}</td>
                        <td>{{ $adjustment->old_stock }}</td>
                        <td>{{ $adjustment->new_stock }}</td>
                        <td>{{ $adjustment->delta }}</td>
                        <td>{{ $adjustment->reason }}</td>
                        <td>{{ $adjustment->user?->name ?? 'System' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $adjustments->links() }}
</section>
@endsection
