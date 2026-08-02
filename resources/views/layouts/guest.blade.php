<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('title', 'Two Fat Cats')</title>
    @include('partials.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div class="auth">
        {{-- Brand panel --}}
        <aside class="auth__brand">
            <div>
                <a href="{{ route('home') }}" class="wordmark">Two Fat Cats</a>
            </div>

            <div>
                <p class="kicker">Pet adoption, done warmly</p>
                <h1>Every pet deserves a second chance.</h1>
                <p>
                    Join a community of adopters, rescuers and sitters finding cats and dogs
                    the homes they deserve — whether you're looking for a new best friend or
                    finding one for someone else.
                </p>
            </div>

            <figure class="auth__quote">
                <blockquote>“We found our cat Biscuit here over a weekend. The whole thing felt personal, not transactional.”</blockquote>
                <figcaption>Priya &amp; Sam — adopted March 2026</figcaption>
            </figure>
        </aside>

        {{-- Form panel --}}
        <main class="auth__form">
            <div class="auth__card">
                <a href="{{ route('home') }}" class="wordmark lg:hidden" style="margin-bottom:2.5rem">Two Fat Cats</a>

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
