@extends('layouts.shop', ['title' => 'Profile | ElectroHub', 'cartCount' => (int) collect(session('cart', []))->sum()])

@section('content')
<section class="profile-shell reveal">
    <div class="panel profile-hero">
        <div>
            <p class="eyebrow">Account Studio</p>
            <h1>Profile Settings</h1>
            <p class="lead">Manage your account details, password security, and delete controls in one clean place.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-secondary">Back to Dashboard</a>
    </div>

    @if (session('status') === 'profile-updated')
        <p class="flash">Profile updated.</p>
    @endif

    @if (session('status') === 'password-updated')
        <p class="flash">Password updated.</p>
    @endif

    <div class="profile-mini">
        <article class="summary">
            <span>User</span>
            <strong>{{ $user->name }}</strong>
        </article>
        <article class="summary">
            <span>Email</span>
            <strong>{{ $user->email }}</strong>
        </article>
        <article class="summary">
            <span>Status</span>
            <strong>{{ $user->is_admin ? 'Admin' : 'Customer' }}</strong>
        </article>
        <article class="summary">
            <span>Addresses</span>
            <a href="{{ route('profile.addresses') }}" class="btn-secondary">Manage</a>
        </article>
    </div>

    <div class="profile-cards">
        <article class="summary profile-card">
            <h3>Profile Information</h3>
            <p>Update your name and email here.</p>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <label for="name">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                @error('name')<p class="empty">{{ $message }}</p>@enderror

                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                @error('email')<p class="empty">{{ $message }}</p>@enderror

                <button type="submit" class="btn-primary full">Save Profile</button>
            </form>
        </article>

        <article class="summary profile-card">
            <h3>Update Password</h3>
            <p>Set a stronger new password.</p>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <label for="current_password">Current Password</label>
                <input id="current_password" type="password" name="current_password" autocomplete="current-password">
                @if ($errors->updatePassword->has('current_password'))
                    <p class="empty">{{ $errors->updatePassword->first('current_password') }}</p>
                @endif

                <label for="password">New Password</label>
                <input id="password" type="password" name="password" autocomplete="new-password">
                @if ($errors->updatePassword->has('password'))
                    <p class="empty">{{ $errors->updatePassword->first('password') }}</p>
                @endif

                <label for="password_confirmation">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">

                <button type="submit" class="btn-primary full">Save Password</button>
            </form>
        </article>

        <article class="summary profile-card">
            <h3>Delete Account</h3>
            <p>Your account will be permanently deleted once you confirm your password.</p>

            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Are you sure you want to delete your account?')">
                @csrf
                @method('DELETE')

                <label for="delete_password">Password</label>
                <input id="delete_password" type="password" name="password" required autocomplete="current-password">
                @if ($errors->userDeletion->has('password'))
                    <p class="empty">{{ $errors->userDeletion->first('password') }}</p>
                @endif

                <button type="submit" class="btn-danger full">Delete Account</button>
            </form>
        </article>
    </div>
</section>
@endsection
