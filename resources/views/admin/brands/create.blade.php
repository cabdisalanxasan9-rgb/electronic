@extends('layouts.shop', ['title' => 'Create Brand | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Create Brand</h1>
        <a href="{{ route('admin.brands.index') }}" class="btn-secondary">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data" class="summary section-space-top">
        @csrf
        
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name') }}" required>
        @error('name')<p class="empty">{{ $message }}</p>@enderror

        <label>Slug (Optional, auto-generated)</label>
        <input type="text" name="slug" value="{{ old('slug') }}">
        @error('slug')<p class="empty">{{ $message }}</p>@enderror

        <label>Description</label>
        <textarea name="description" rows="3">{{ old('description') }}</textarea>
        @error('description')<p class="empty">{{ $message }}</p>@enderror

        <label>Logo / Image</label>
        <input type="file" name="image" accept="image/*">
        @error('image')<p class="empty">{{ $message }}</p>@enderror

        <button type="submit" class="btn-primary">Save Brand</button>
    </form>
</section>
@endsection
