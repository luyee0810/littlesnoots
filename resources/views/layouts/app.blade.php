<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Two Fat Cats — Pet Adoption')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased flex flex-col">

    <header class="sticky top-0 z-30 border-b border-stone-200 bg-stone-50/80 backdrop-blur">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold text-lg">
                <span class="text-2xl">🐾</span>
                <span>Two Fat Cats</span>
            </a>
            <div class="flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('pets.index') }}" class="hover:text-amber-700">Adopt</a>
                <span class="cursor-not-allowed text-stone-400" title="Coming soon">Services</span>
                <span class="cursor-not-allowed text-stone-400" title="Coming soon">Shop</span>
                @auth
                    <a href="{{ route('dashboard') }}" class="hover:text-amber-700">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="flex items-center">
                        @csrf
                        <button type="submit" class="text-stone-500 hover:text-amber-700">Log out</button>
                    </form>
                    <a href="{{ route('pets.index') }}"
                       class="rounded-full bg-amber-600 px-4 py-1.5 text-white shadow-sm transition hover:bg-amber-700">
                        Find a pet
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-amber-700">Log in</a>
                    <a href="{{ route('register') }}"
                       class="rounded-full bg-amber-600 px-4 py-1.5 text-white shadow-sm transition hover:bg-amber-700">
                        Sign up
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    @if (session('success') || session('error'))
        <div class="mx-auto mt-4 w-full max-w-6xl px-4">
            @if (session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-stone-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-8 text-sm text-stone-500 sm:flex-row">
            <p>© {{ date('Y') }} Two Fat Cats. Giving pets a second chance.</p>
            <p>Made with 🐾 in Laravel</p>
        </div>
    </footer>

</body>
</html>
