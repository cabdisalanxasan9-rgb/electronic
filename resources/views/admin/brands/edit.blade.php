@extends('layouts.shop', ['title' => 'Edit Brand | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Edit Brand</h1>
        <a href="{{ route('admin.brands.index') }}" class="btn-secondary">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data" class="summary section-space-top">
        @csrf
        @method('PUT')
        
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name', $brand->name) }}" required>
        @error('name')<p class="empty">{{ $message }}</p>@enderror

        <label>Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $brand->slug) }}">
        @error('slug')<p class="empty">{{ $message }}</p>@enderror

        <label>Description</label>
        <textarea name="description" rows="3">{{ old('description', $brand->description) }}</textarea>
        @error('description')<p class="empty">{{ $message }}</p>@enderror

        <label>Logo / Image</label>
        @if($brand->image_path)
            <div style="margin-bottom: 1rem;">
                <img src="{{ Storage::url($brand->image_path) }}" alt="{{ $brand->name }}" style="max-width: 150px;">
            </div>
        @endif
        <input type="file" name="image" accept="image/*">
        <small class="text-muted">Leave empty to keep current image.</small>
        @error('image')<p class="empty">{{ $message }}</p>@enderror

        <button type="submit" class="btn-primary" style="margin-top: 1rem;">Update Brand</button>
    </form>
</section>
@endsection
