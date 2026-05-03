@extends('layouts.shop', ['title' => 'Compare Products | ElectroHub', 'cartCount' => $cartCount])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Compare Products</h1>
        <div class="admin-actions">
            <a href="{{ route('shop.home') }}" class="btn-secondary">Back to Store</a>
            @if ($products->isNotEmpty())
                <form method="POST" action="{{ route('compare.clear') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Clear Compare</button>
                </form>
            @endif
        </div>
    </div>

    @if ($products->isEmpty())
        <p class="empty">No products to compare yet.</p>
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Attribute</th>
                        @foreach ($products as $product)
                            <th>
                                {{ $product->name }}
                                <form method="POST" action="{{ route('compare.remove', $product) }}" style="margin-top:6px;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Remove</button>
                                </form>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Category</td>
                        @foreach ($products as $product)
                            <td>{{ $product->category }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Price</td>
                        @foreach ($products as $product)
                            <td>${{ number_format((float) $product->price, 2) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Rating</td>
                        @foreach ($products as $product)
                            <td>{{ number_format((float) $product->rating, 1) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Stock</td>
                        @foreach ($products as $product)
                            <td>{{ $product->stock }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Badge</td>
                        @foreach ($products as $product)
                            <td>{{ $product->badge ?: '-' }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
