<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Two Fat Cats')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        .animate-rise { animation: rise .55s cubic-bezier(.2,.7,.2,1) both; }
        @media (prefers-reduced-motion: reduce) { .animate-rise { animation: none; } }
    </style>
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased">
    <div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">

        {{-- Brand panel --}}
        <aside class="relative hidden overflow-hidden bg-gradient-to-br from-amber-700 via-amber-600 to-orange-500 lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16">
            <div aria-hidden="true" class="absolute inset-0"
                 style="background-image:url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='72' height='72' viewBox='0 0 72 72'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cellipse cx='36' cy='45' rx='12' ry='10'/%3E%3Ccircle cx='22' cy='29' r='5'/%3E%3Ccircle cx='36' cy='24' r='5'/%3E%3Ccircle cx='50' cy='29' r='5'/%3E%3C/g%3E%3C/svg%3E&quot;);background-size:72px 72px;"></div>
            <div aria-hidden="true" class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/10 blur-2xl"></div>

            <div class="relative">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-white">
                    <span class="text-2xl">🐾</span>
                    <span class="text-lg font-semibold">Two Fat Cats</span>
                </a>
            </div>

            <div class="relative max-w-md">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-amber-100/80">Pet adoption, done warmly</p>
                <h1 class="mt-5 text-4xl font-semibold leading-[1.05] tracking-tight text-white xl:text-5xl">
                    Every pet deserves a second chance.
                </h1>
                <p class="mt-5 text-[15px] leading-relaxed text-amber-50/90">
                    Join a community of adopters and rescuers finding cats and dogs the homes they deserve —
                    whether you're looking for a new best friend or finding one for someone else.
                </p>

                <div class="mt-8 flex items-center gap-3">
                    <div class="flex -space-x-2">
                        @foreach (['🐱', '🐶', '🐰', '🐹'] as $critter)
                            <span class="grid h-10 w-10 place-items-center rounded-full border-2 border-amber-600 bg-amber-100 text-lg shadow-sm">{{ $critter }}</span>
                        @endforeach
                    </div>
                    <span class="text-sm font-medium text-amber-50/90">and 300+ more looking for a home</span>
                </div>
            </div>

            <figure class="relative max-w-md">
                <blockquote class="text-[15px] leading-relaxed text-amber-50/95">
                    &ldquo;We found our cat Biscuit here over a weekend. The whole thing felt personal, not transactional.&rdquo;
                </blockquote>
                <figcaption class="mt-3 text-sm text-amber-100/70">Priya &amp; Sam — adopted March 2026</figcaption>
            </figure>
        </aside>

        {{-- Form panel --}}
        <main class="flex flex-col justify-center px-5 py-12 sm:px-8">
            <div class="animate-rise mx-auto w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-10 inline-flex items-center gap-2 lg:hidden">
                    <span class="text-2xl">🐾</span>
                    <span class="text-lg font-semibold">Two Fat Cats</span>
                </a>

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
