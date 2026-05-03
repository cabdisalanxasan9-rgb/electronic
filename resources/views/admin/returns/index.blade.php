@extends('layouts.shop', ['title' => 'Admin Returns | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Admin: Returns</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Dashboard</a>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th></th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Reason</th>
                    <th>Updated</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($returns as $return)
                    <tr>
                        <td>{{ $return->order?->order_number }}</td>
                        <td>
                            @if ($return->order)
                                <a href="{{ route('admin.orders.show', $return->order) }}" class="btn-secondary">View</a>
                            @endif
                        </td>
                        <td>{{ $return->user?->email }}</td>
                        <td>{{ ucfirst($return->status) }}</td>
                        <td>{{ $return->reason }}</td>
                        <td>{{ $return->updated_at?->format('Y-m-d') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.returns.update', $return) }}" class="table-actions">
                                @csrf
                                @method('PATCH')
                                <select name="status">
                                    @foreach (['pending', 'approved', 'rejected', 'refunded'] as $status)
                                        <option value="{{ $status }}" @selected($return->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="admin_note" placeholder="Note" value="{{ $return->admin_note }}">
                                <button type="submit" class="btn-secondary">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No returns found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $returns->links() }}
</section>
@endsection
