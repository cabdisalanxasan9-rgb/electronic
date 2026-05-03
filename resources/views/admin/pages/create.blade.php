@extends('layouts.shop', ['title' => 'Create Page | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Create Page</h1>
        <a href="{{ route('admin.pages.index') }}" class="btn-secondary">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.pages.store') }}" class="summary section-space-top">
        @csrf
        
        <label>Title</label>
        <input type="text" name="title" value="{{ old('title') }}" required>
        @error('title')<p class="empty">{{ $message }}</p>@enderror

        <label>Slug (Optional, auto-generated)</label>
        <input type="text" name="slug" value="{{ old('slug') }}">
        @error('slug')<p class="empty">{{ $message }}</p>@enderror

        <label>Content</label>
        <textarea name="content" rows="10" required style="font-family: monospace;">{{ old('content') }}</textarea>
        @error('content')<p class="empty">{{ $message }}</p>@enderror

        <label class="product-check-row" style="margin: 1rem 0;">
            <input type="checkbox" name="is_published" value="1" checked>
            Publish immediately
        </label>

        <button type="submit" class="btn-primary">Save Page</button>
    </form>
</section>
@endsection
