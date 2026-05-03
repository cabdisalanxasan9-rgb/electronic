@extends('layouts.shop', ['title' => 'Admin Pages | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Static Pages</h1>
        <div>
            <a href="{{ route('admin.pages.create') }}" class="btn-primary">Add Page</a>
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Dashboard</a>
        </div>
    </div>

    <div class="summary section-space-top">
        @forelse ($pages as $page)
            <div class="feed-item">
                <strong>{{ $page->title }}</strong>
                <span>Slug: {{ $page->slug }}</span>
                <span>Status: {{ $page->is_published ? 'Published' : 'Draft' }}</span>
                <div class="actions">
                    <a href="{{ route('admin.pages.edit', $page) }}" class="btn-secondary">Edit</a>
                    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" style="display:inline;" onsubmit="return confirm('Delete this page?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn-danger" type="submit">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="empty">No pages found.</p>
        @endforelse
    </div>
    
    @if($pages->hasPages())
        <div class="section-space-top">
            {{ $pages->links() }}
        </div>
    @endif
</section>
@endsection
