@extends('layouts.shop', ['title' => 'Admin Contacts | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Contact Messages</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Dashboard</a>
    </div>

    <div class="summary section-space-top">
        @forelse ($contacts as $contact)
            <div class="feed-item" style="border-left: 4px solid {{ $contact->is_read ? 'var(--border)' : 'var(--primary)' }}; padding-left: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <strong>{{ $contact->subject }}</strong>
                        <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 0.2rem;">
                            <span>From: {{ $contact->name }} ({{ $contact->email }})</span> | 
                            <span>{{ $contact->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @if(!$contact->is_read)
                        <span class="badge" style="background: var(--primary); color: white; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">New</span>
                    @elseif($contact->admin_reply)
                        <span class="badge" style="background: var(--success, green); color: white; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Replied</span>
                    @endif
                </div>
                
                <div class="actions" style="margin-top: 1rem;">
                    <a href="{{ route('admin.contacts.show', $contact) }}" class="btn-secondary">View & Reply</a>
                    <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}" style="display:inline;" onsubmit="return confirm('Delete this message?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn-danger" type="submit">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="empty">No contact messages found.</p>
        @endforelse
    </div>
    
    @if($contacts->hasPages())
        <div class="section-space-top">
            {{ $contacts->links() }}
        </div>
    @endif
</section>
@endsection
