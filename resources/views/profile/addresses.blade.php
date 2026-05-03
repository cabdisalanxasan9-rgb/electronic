@extends('layouts.shop', ['title' => 'Addresses | ElectroHub', 'cartCount' => $cartCount])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Saved Addresses</h1>
        <a href="{{ route('profile.edit') }}" class="btn-secondary">Back to Profile</a>
    </div>

    <form method="POST" action="{{ route('profile.addresses.store') }}" class="summary">
        @csrf
        <h2>Add Address</h2>
        <label>Name</label>
        <input type="text" name="name" required>

        <label>Phone</label>
        <input type="text" name="phone" required>

        <label>Address Line 1</label>
        <input type="text" name="line1" required>

        <label>Address Line 2</label>
        <input type="text" name="line2">

        <label>City</label>
        <input type="text" name="city" required>

        <label>State</label>
        <input type="text" name="state">

        <label>Postal Code</label>
        <input type="text" name="postal_code">

        <label>Country</label>
        <input type="text" name="country" value="Somalia" required>

        <label class="product-check-row">
            <input type="checkbox" name="is_default" value="1">
            Set as default
        </label>

        <button class="btn-primary" type="submit">Save Address</button>
    </form>

    <div class="summary">
        <h2>Your Addresses</h2>
        @forelse ($addresses as $address)
            <div class="feed-item">
                <strong>{{ $address->name }}</strong>
                @if ($address->is_default)
                    <span class="badge">Default</span>
                @endif
                <p>{{ $address->phone }}</p>
                <p>{{ $address->line1 }} {{ $address->line2 }}</p>
                <p>{{ $address->city }} {{ $address->state }} {{ $address->postal_code }}</p>
                <p>{{ $address->country }}</p>
                <div class="admin-actions">
                    <form method="POST" action="{{ route('profile.addresses.update', $address) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="name" value="{{ $address->name }}">
                        <input type="hidden" name="phone" value="{{ $address->phone }}">
                        <input type="hidden" name="line1" value="{{ $address->line1 }}">
                        <input type="hidden" name="line2" value="{{ $address->line2 }}">
                        <input type="hidden" name="city" value="{{ $address->city }}">
                        <input type="hidden" name="state" value="{{ $address->state }}">
                        <input type="hidden" name="postal_code" value="{{ $address->postal_code }}">
                        <input type="hidden" name="country" value="{{ $address->country }}">
                        <input type="hidden" name="is_default" value="1">
                        <button type="submit" class="btn-secondary">Make Default</button>
                    </form>
                    <form method="POST" action="{{ route('profile.addresses.destroy', $address) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="empty">No saved addresses.</p>
        @endforelse
    </div>
</section>
@endsection
