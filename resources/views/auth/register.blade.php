<x-guest-layout>
    <h2 class="auth-title">Register</h2>
    <p class="auth-subtitle">Create an account to get started.</p>

    <form method="POST" action="{{ route('register') }}" class="auth-form js-auth-submit">
        @csrf

        <label for="name">Name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password">

        <label for="password_confirmation">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">

        <button type="submit" class="btn-primary full auth-submit-btn">
            <span class="btn-label">Register</span>
            <span class="btn-spinner" aria-hidden="true"></span>
        </button>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">Already have an account? Login</a>
    </div>
</x-guest-layout>
