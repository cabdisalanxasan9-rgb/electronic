@extends('layouts.shop', ['title' => 'Edit Category | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Edit Category</h1>
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="summary section-space-top">
        @csrf
        @method('PUT')
        
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name', $category->name) }}" required>
        @error('name')<p class="empty">{{ $message }}</p>@enderror

        <label>Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $category->slug) }}">
        @error('slug')<p class="empty">{{ $message }}</p>@enderror

        <label>Description</label>
        <textarea name="description" rows="3">{{ old('description', $category->description) }}</textarea>
        @error('description')<p class="empty">{{ $message }}</p>@enderror

        <button type="submit" class="btn-primary">Update Category</button>
    </form>
</section>
@endsection
