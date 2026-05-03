@extends('layouts.shop', ['title' => 'Edit Page | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Edit Page</h1>
        <a href="{{ route('admin.pages.index') }}" class="btn-secondary">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="summary section-space-top">
        @csrf
        @method('PUT')
        
        <label>Title</label>
        <input type="text" name="title" value="{{ old('title', $page->title) }}" required>
        @error('title')<p class="empty">{{ $message }}</p>@enderror

        <label>Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $page->slug) }}">
        @error('slug')<p class="empty">{{ $message }}</p>@enderror

        <label>Content</label>
        <textarea name="content" rows="10" required style="font-family: monospace;">{{ old('content', $page->content) }}</textarea>
        @error('content')<p class="empty">{{ $message }}</p>@enderror

        <label class="product-check-row" style="margin: 1rem 0;">
            <input type="checkbox" name="is_published" value="1" {{ $page->is_published ? 'checked' : '' }}>
            Publish
        </label>

        <button type="submit" class="btn-primary">Update Page</button>
    </form>
</section>
@endsection
