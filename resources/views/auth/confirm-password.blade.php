<x-guest-layout>
    <h2 class="auth-title">Confirm Password</h2>
    <p class="auth-subtitle">This is a secure area. Please confirm your password.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="auth-form js-auth-submit">
        @csrf

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">

        <button type="submit" class="btn-primary full auth-submit-btn">
            <span class="btn-label">Confirm</span>
            <span class="btn-spinner" aria-hidden="true"></span>
        </button>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">Back to Login</a>
    </div>
</x-guest-layout>
