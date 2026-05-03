<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} | Account</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Chakra+Petch:wght@500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="site-bg">
            <div class="glow glow-a"></div>
            <div class="glow glow-b"></div>
            <div class="grid-overlay"></div>
        </div>

        <div class="auth-shell wrapper reveal">
            <section class="auth-hero">
                <a href="{{ route('shop.home') }}" class="brand">
                    <span class="brand-mark">EH</span>
                    <span>
                        <strong>ElectroHub</strong>
                        <small>Secure Access</small>
                    </span>
                </a>

                <h1>Welcome to ElectroHub</h1>
                <p>Log in or register to complete checkout, view your orders, and access the dashboard.</p>
                <a href="{{ route('shop.home') }}" class="btn-secondary">Back to Store</a>
            </section>

            <section class="auth-card">
                <div
                    class="auth-toast-payload"
                    data-toast-success="{{ session('status') ? e((string) session('status')) : '' }}"
                    data-toast-error="{{ $errors->any() ? e((string) $errors->first()) : '' }}"
                    hidden
                ></div>

                {{ $slot }}
            </section>
        </div>
    </body>
</html>
