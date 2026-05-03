<x-guest-layout>
    <h2 class="auth-title">New Password</h2>
    <p class="auth-subtitle">Enter a new password to regain access to your account.</p>

    <form method="POST" action="{{ route('password.store') }}" class="auth-form js-auth-submit">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password">

        <label for="password_confirmation">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">

        <button type="submit" class="btn-primary full auth-submit-btn">
            <span class="btn-label">Reset Password</span>
            <span class="btn-spinner" aria-hidden="true"></span>
        </button>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">Back to Login</a>
    </div>
</x-guest-layout>
