@extends('layouts.shop', ['title' => 'Admin Users | ElectroHub', 'cartCount' => 0])

@section('content')
<section class="panel reveal">
    <div class="section-head">
        <h1>Admin: Users</h1>
        <div class="admin-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Dashboard</a>
            <a href="{{ route('admin.roles.index') }}" class="btn-secondary">Roles</a>
        </div>
    </div>

    <form method="GET" class="filters filters-admin-users">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search name/email...">
        <button class="btn-secondary" type="submit">Search</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->roleLabel() }}</td>
                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.users.role', $user) }}" class="table-actions">
                                @csrf
                                @method('PATCH')
                                <select name="role">
                                    <option value="customer" @selected($user->role === 'customer')>Customer</option>
                                    <option value="sales_admin" @selected($user->role === 'sales_admin')>Sales Admin</option>
                                    <option value="inventory_admin" @selected($user->role === 'inventory_admin')>Inventory Admin</option>
                                    <option value="super_admin" @selected($user->role === 'super_admin')>Super Admin</option>
                                </select>
                                <button class="btn-secondary" type="submit">Update Role</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</section>
@endsection
