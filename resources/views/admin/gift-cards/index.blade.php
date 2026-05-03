@extends('layouts.shop', ['title' => 'Admin Gift Cards | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Admin: Gift Cards</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Dashboard</a>
    </div>

    <form method="POST" action="{{ route('admin.gift-cards.store') }}" class="summary">
        @csrf
        <h2>Create Gift Card</h2>
        <label>Code</label>
        <input type="text" name="code" required>
        <label>Initial Balance</label>
        <input type="number" step="0.01" name="initial_balance" required>
        <label>Expires At</label>
        <input type="datetime-local" name="expires_at">
        <label class="product-check-row">
            <input type="checkbox" name="is_active" value="1" checked>
            Active
        </label>
        <button type="submit" class="btn-primary">Save Gift Card</button>
    </form>

    <div class="summary section-space-top">
        <h2>Gift Cards</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Expires</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cards as $card)
                        <tr>
                            <td>{{ $card->code }}</td>
                            <td>${{ number_format((float) $card->balance, 2) }}</td>
                            <td>{{ $card->is_active ? 'Active' : 'Disabled' }}</td>
                            <td>{{ $card->expires_at?->format('Y-m-d') ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.gift-cards.update', $card) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $card->is_active ? 0 : 1 }}">
                                    <button type="submit" class="btn-secondary">
                                        {{ $card->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No gift cards yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $cards->links() }}
    </div>
</section>
@endsection
