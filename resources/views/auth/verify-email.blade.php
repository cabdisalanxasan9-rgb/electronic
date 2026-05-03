<x-guest-layout>
    <h2 class="auth-title">Verify Email</h2>
    <p class="auth-subtitle">We have sent you a verification link. Please check your email and click the link before continuing.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="flash">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <div class="auth-links" style="margin-top: 4px;">
        <form method="POST" action="{{ route('verification.send') }}" class="js-auth-submit">
            @csrf

            <button type="submit" class="btn-primary auth-submit-btn">
                <span class="btn-label">Resend Verification Email</span>
                <span class="btn-spinner" aria-hidden="true"></span>
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn-secondary">Log Out</button>
        </form>
    </div>
</x-guest-layout>
