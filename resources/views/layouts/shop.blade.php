<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'ElectroHub' }}</title>
    <meta name="description" content="{{ $description ?? 'ElectroHub brings modern electronics, accessories, and fast delivery.' }}">
    <meta property="og:title" content="{{ $title ?? 'ElectroHub' }}">
    <meta property="og:description" content="{{ $description ?? 'ElectroHub brings modern electronics, accessories, and fast delivery.' }}">
    @isset($image)
        <meta property="og:image" content="{{ $image }}">
    @endisset
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Chakra+Petch:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="site-bg">
        <div class="glow glow-a"></div>
        <div class="glow glow-b"></div>
        <div class="grid-overlay"></div>
    </div>

    <header class="topbar wrapper reveal">
        <a href="{{ route('shop.home') }}" class="brand">
            <span class="brand-mark">EH</span>
            <span>
                <strong>ElectroHub</strong>
                <small>Smart Electronics Store</small>
            </span>
        </a>
        <button type="button" class="menu-toggle" aria-label="Open menu" aria-controls="site-nav" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <button type="button" class="theme-toggle" aria-label="Toggle theme" title="Toggle theme">
            <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
        </button>
        <nav class="nav-links" id="site-nav">
            <a href="{{ route('shop.home') }}">Store</a>
            <a href="{{ route('shop.cart') }}">Cart <span class="cart-pill">{{ $cartCount ?? 0 }}</span></a>
            @auth
                <a href="{{ route('wishlist.index') }}">Wishlist</a>
                <a href="{{ route('orders.index') }}">Orders</a>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                    @if (auth()->user()->canManageOrders())
                        <a href="{{ route('admin.orders.index') }}">Manage Orders</a>
                    @endif
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-btn">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
            @endauth
        </nav>
    </header>

    <main class="wrapper">
        <div
            class="toast-payload"
            data-toast-success="{{ session('status') ? e((string) session('status')) : '' }}"
            data-toast-error="{{ $errors->any() ? e((string) $errors->first()) : '' }}"
            hidden
        ></div>

        @yield('content')
    </main>

    <footer class="wrapper footer reveal">
        <p>ElectroHub © {{ now()->year }}. Quality products, fair prices.</p>
    </footer>
</body>
</html>
