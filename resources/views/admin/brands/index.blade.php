@extends('layouts.shop', ['title' => 'Admin Brands | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Brands</h1>
        <div>
            <a href="{{ route('admin.brands.create') }}" class="btn-primary">Add Brand</a>
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Dashboard</a>
        </div>
    </div>

    <div class="summary section-space-top">
        @forelse ($brands as $brand)
            <div class="feed-item" style="display: flex; align-items: center; gap: 1rem;">
                @if($brand->image_path)
                    <img src="{{ Storage::url($brand->image_path) }}" alt="{{ $brand->name }}" style="width: 50px; height: 50px; object-fit: contain;">
                @else
                    <div style="width: 50px; height: 50px; background: var(--border); display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">No Img</div>
                @endif
                <div style="flex: 1;">
                    <strong>{{ $brand->name }}</strong>
                    <div style="font-size: 0.9rem; color: var(--text-muted);">
                        <span>Slug: {{ $brand->slug }}</span> | 
                        <span>Products: {{ $brand->products_count }}</span>
                    </div>
                </div>
                <div class="actions">
                    <a href="{{ route('admin.brands.edit', $brand) }}" class="btn-secondary">Edit</a>
                    <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" style="display:inline;" onsubmit="return confirm('Delete this brand?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn-danger" type="submit">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="empty">No brands found.</p>
        @endforelse
    </div>
    
    @if($brands->hasPages())
        <div class="section-space-top">
            {{ $brands->links() }}
        </div>
    @endif
</section>
@endsection
