<x-guest-layout>
    <h2 class="auth-title">Login</h2>
    <p class="auth-subtitle">Sign in to continue shopping.</p>

    <form method="POST" action="{{ route('login') }}" class="auth-form js-auth-submit">
        @csrf

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">

        <label class="auth-check">
            <input id="remember_me" type="checkbox" name="remember">
            <span>Remember me</span>
        </label>

        <button type="submit" class="btn-primary full auth-submit-btn">
            <span class="btn-label">Log in</span>
            <span class="btn-spinner" aria-hidden="true"></span>
        </button>
    </form>

    <div class="auth-links">
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}">Forgot password?</a>
        @endif
        <a href="{{ route('register') }}">No account? Register</a>
    </div>
</x-guest-layout>
