@extends('layouts.shop', ['title' => 'Admin Products | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Admin: Products</h1>
        <div class="admin-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Dashboard</a>
            <a href="{{ route('admin.inventory.index') }}" class="btn-secondary">Inventory History</a>
            @if (auth()->user()->canManageUsers())
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Users</a>
            @endif
            <a href="{{ route('admin.products.create') }}" class="btn-primary">+ New Product</a>
        </div>
    </div>

    <form method="GET" class="filters filters-admin-products">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search products...">

        <select name="category">
            <option value="all" @selected($activeCategory === 'all')>All Categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected($activeCategory === $category)>{{ $category }}</option>
            @endforeach
        </select>

        <select name="trashed">
            <option value="without" @selected($trashed === 'without')>Active Only</option>
            <option value="with" @selected($trashed === 'with')>Include Deleted</option>
            <option value="only" @selected($trashed === 'only')>Deleted Only</option>
        </select>

        <button class="btn-secondary" type="submit">Filter</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Featured</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>
                            <div class="thumb-row">
                                <img src="{{ $product->display_image }}" alt="{{ $product->name }}" loading="lazy" decoding="async" fetchpriority="low">
                                <span>{{ $product->name }}</span>
                            </div>
                        </td>
                        <td>{{ $product->category }}</td>
                        <td>${{ number_format((float) $product->price, 2) }}</td>
                        <td>{{ $product->stock }} base / {{ $product->variants()->where('is_active', true)->count() }} variants</td>
                        <td>{{ $product->featured ? 'Yes' : 'No' }}</td>
                        <td class="table-actions">
                            @if ($product->trashed())
                                <form method="POST" action="{{ route('admin.products.restore', $product->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-secondary">Restore</button>
                                </form>
                            @else
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $products->links() }}
</section>
@endsection
