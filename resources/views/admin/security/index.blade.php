@extends('layouts.shop', ['title' => 'Security | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Security</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Dashboard</a>
    </div>

    <div class="summary">
        <h2>Hardening Checklist</h2>
        <p><span>Admin passwords</span><strong>Minimum 12 chars, mixed case, numbers, symbols</strong></p>
        <p><span>Login throttling</span><strong>Enabled</strong></p>
        <p><span>Production debug</span><strong>Must be false</strong></p>
        <p><span>HTTPS cookies</span><strong>Enable secure session cookies in production</strong></p>
        <p><span>Audit logs</span><strong>Product, order, user role actions tracked</strong></p>
    </div>

    <div class="summary section-space-top">
        <h2>Environment Checks</h2>
        @foreach ($checks as $name => $value)
            <p><span>{{ $name }}</span><strong>{{ $value }}</strong></p>
        @endforeach
    </div>
</section>
@endsection
