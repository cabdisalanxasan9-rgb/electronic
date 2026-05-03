<x-guest-layout>
    <h2 class="auth-title">Reset Link</h2>
    <p class="auth-subtitle">Enter your email and we'll send you a link to reset your password.</p>

    <form method="POST" action="{{ route('password.email') }}" class="auth-form js-auth-submit">
        @csrf

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        <button type="submit" class="btn-primary full auth-submit-btn">
            <span class="btn-label">Email Password Reset Link</span>
            <span class="btn-spinner" aria-hidden="true"></span>
        </button>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">Back to Login</a>
    </div>
</x-guest-layout>
