@extends('layouts.shop', ['title' => 'Create Product | Admin', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>New Product</h1>
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">Back</a>
    </div>

    @include('admin.products.form', [
        'action' => route('admin.products.store'),
        'method' => 'POST',
        'product' => null,
    ])
</section>
@endsection
