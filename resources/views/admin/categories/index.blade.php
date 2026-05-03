@extends('layouts.shop', ['title' => 'Admin Categories | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Categories</h1>
        <div>
            <a href="{{ route('admin.categories.create') }}" class="btn-primary">Add Category</a>
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Dashboard</a>
        </div>
    </div>

    <div class="summary section-space-top">
        @forelse ($categories as $category)
            <div class="feed-item">
                <strong>{{ $category->name }}</strong>
                <span>Slug: {{ $category->slug }}</span>
                <span>Products: {{ $category->products_count }}</span>
                <div class="actions">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn-secondary">Edit</a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn-danger" type="submit">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="empty">No categories found.</p>
        @endforelse
    </div>
    
    @if($categories->hasPages())
        <div class="section-space-top">
            {{ $categories->links() }}
        </div>
    @endif
</section>
@endsection
