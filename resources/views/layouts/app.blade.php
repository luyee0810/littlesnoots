<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Meet adoptable pets and find trusted local pet care with Two Fat Cats.">
    <title>@yield('title', 'Two Fat Cats — Pet Adoption')</title>
    @include('partials.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col antialiased">
    <a href="#main" class="sr-only focus:not-sr-only">Skip to content</a>

    <header class="site-header">
        <nav class="shell header-nav" aria-label="Main navigation">
            <a href="{{ route('home') }}" class="brand" aria-label="Two Fat Cats home">
                <img src="{{ asset('images/logo.png') }}" alt="Two Fat Cats" width="749" height="391" fetchpriority="high">
            </a>

            @php
                $nav = [
                    ['label' => 'Adopt', 'href' => route('pets.index'), 'on' => request()->routeIs('pets.*')],
                    ['label' => 'Pet care', 'href' => route('services.index'), 'on' => request()->routeIs('services.*', 'providers.*')],
                    ['label' => 'Memorials', 'href' => route('memorials.index'), 'on' => request()->routeIs('memorials.*')],
                ];
            @endphp

            <div class="desktop-nav">
                @foreach ($nav as $item)
                    <a href="{{ $item['href'] }}" class="nav-link" @if ($item['on']) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </div>

            <div class="header-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="icon-button" aria-label="Open dashboard"><i data-lucide="user-round" aria-hidden="true"></i></a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn--outline btn--sm">Log out</button></form>
                @else
                    <a href="{{ route('login') }}" class="nav-link header-login">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn--accent btn--sm">Join us</a>
                @endauth
            </div>

            <details class="mobile-menu">
                <summary class="icon-button" aria-label="Open navigation menu"><i data-lucide="menu" aria-hidden="true"></i></summary>
                <div class="mobile-menu-panel">
                    @foreach ($nav as $item)
                        <a href="{{ $item['href'] }}" class="mobile-nav-link">{{ $item['label'] }}</a>
                    @endforeach
                    @auth
                        <a href="{{ route('dashboard') }}" class="mobile-nav-link">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="mobile-nav-link">Log out</button></form>
                    @else
                        <a href="{{ route('login') }}" class="mobile-nav-link">Log in</a>
                        <a href="{{ route('register') }}" class="btn btn--accent btn--block" style="margin-top:.5rem">Join us</a>
                    @endauth
                </div>
            </details>
        </nav>
    </header>

    @if (session('success') || session('error'))
        <div class="shell flash-wrap">
            @if (session('success'))<div class="alert alert--ok">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="alert alert--bad">{{ session('error') }}</div>@endif
        </div>
    @endif

    <main id="main" class="flex-1">@yield('content')</main>

    <footer class="site-footer">
        <div class="shell">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="{{ route('home') }}" class="brand brand--footer">
                        <img src="{{ asset('images/logo.png') }}" alt="Two Fat Cats" width="749" height="391" loading="lazy">
                    </a>
                    <p>Connecting loving pets with loving people — and the sitters who look after them. Because every pet deserves a home.</p>
                </div>
                <div>
                    <h3>Explore</h3>
                    <nav>
                        <a href="{{ route('pets.index') }}">Adopt a pet</a>
                        <a href="{{ route('services.index') }}">Find a sitter</a>
                        <a href="{{ route('memorials.index') }}">Pet memorials</a>
                    </nav>
                </div>
                <div>
                    <h3>Support</h3>
                    <nav>
                        <a href="{{ route('home') }}#guides">Guides &amp; resources</a>
                        <a href="{{ route('provider.onboarding') }}">Become a sitter</a>
                    </nav>
                </div>
                <div>
                    <h3>Account</h3>
                    <nav>
                        @auth
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}">Log in</a>
                            <a href="{{ route('register') }}">Create account</a>
                        @endauth
                    </nav>
                </div>
            </div>
            <div class="footer-bottom">
                <span>© {{ date('Y') }} Two Fat Cats. All rights reserved.</span>
                <span>Made in Kuala Lumpur 🐾</span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
