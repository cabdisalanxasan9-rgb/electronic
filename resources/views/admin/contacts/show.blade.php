@extends('layouts.shop', ['title' => 'View Contact | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>View Message</h1>
        <a href="{{ route('admin.contacts.index') }}" class="btn-secondary">Back</a>
    </div>

    <div class="summary section-space-top">
        <div style="margin-bottom: 2rem;">
            <h3>Subject: {{ $contact->subject }}</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">From: {{ $contact->name }} ({{ $contact->email }}) - {{ $contact->created_at->format('M d, Y H:i') }}</p>
            
            <div style="background: var(--panel-bg); padding: 1rem; border: 1px solid var(--border); border-radius: 8px; margin-top: 1rem;">
                {{ nl2br(e($contact->message)) }}
            </div>
        </div>

        <form method="POST" action="{{ route('admin.contacts.update', $contact) }}">
            @csrf
            @method('PUT')
            
            <label>Admin Reply</label>
            <textarea name="admin_reply" rows="6" placeholder="Write your reply to the customer here...">{{ old('admin_reply', $contact->admin_reply) }}</textarea>
            @error('admin_reply')<p class="empty">{{ $message }}</p>@enderror

            <button type="submit" class="btn-primary" style="margin-top: 1rem;">Save Reply</button>
        </form>
    </div>
</section>
@endsection
