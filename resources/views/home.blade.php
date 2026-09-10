@extends('layouts.app')

@section('title', 'Two Fat Cats — Find your new best friend')

@section('content')
    {{-- ---- Hero ---------------------------------------------------------- --}}
    <section class="hero hero--bg">
        <video class="hero__bg" autoplay loop muted playsinline preload="metadata"
               poster="{{ asset('images/dogcat-poster.jpg') }}" aria-hidden="true">
            <source src="{{ asset('videos/dogcat.mp4') }}" type="video/mp4">
        </video>
        <div class="shell hero__grid">
            <div>
                <p class="kicker">Adopt · Love · Thrive</p>
                <h1>Find the companion who changes <em>everything</em></h1>
                <p class="lede">
                    Meet cats and dogs waiting for a second chance, and book trusted local
                    sitters to look after them once they're home.
                </p>

                <div class="hero__actions">
                    <a href="{{ route('pets.index') }}" class="btn btn--accent">Adopt a pet <i data-lucide="paw-print" aria-hidden="true"></i></a>
                    <a href="{{ route('services.index') }}" class="btn btn--outline">Find pet care <i data-lucide="arrow-right" aria-hidden="true"></i></a>
                </div>

                <div class="hero__adopters">
                    <div class="avatar-stack" aria-hidden="true">
                        @foreach ([32, 12, 45, 5] as $face)
                            <img src="https://i.pravatar.cc/88?img={{ $face }}" alt="" width="44" height="44">
                        @endforeach
                    </div>
                    <p>Join <strong>120K+</strong> happy adopters</p>
                </div>
            </div>

            <div class="hero__visual">
                <div class="hero__frame">
                    <video class="hero__video" autoplay loop muted playsinline preload="metadata"
                           poster="{{ asset('images/dogcat-poster.jpg') }}" aria-hidden="true">
                        <source src="{{ asset('videos/dogcat.mp4') }}" type="video/mp4">
                    </video>
                </div>
            </div>
        </div>

        {{-- Proof bar: closes the hero, spanning both columns. --}}
        <div class="shell">
            <div class="hero__stat-bar">
                @foreach ([
                    ['paw-print', 'blush', '12,000+', 'Pets available'],
                    ['heart', 'sage', '20,000+', 'Successful adoptions'],
                    ['house', 'butter', '980+', 'Shelters & rescues'],
                    ['shield-check', 'sky', '100%', 'Verified & safe'],
                ] as [$icon, $tint, $figure, $label])
                    <div class="hero__stat">
                        <span class="hero__stat-icon hero__stat-icon--{{ $tint }}" aria-hidden="true">
                            <i data-lucide="{{ $icon }}"></i>
                        </span>
                        <div>
                            <b>{{ $figure }}</b>
                            <small>{{ $label }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---- Featured pets -------------------------------------------------- --}}
    <section class="band band--raised" id="adopt">
        <div class="shell">
            <div class="band-head">
                <div>
                    <p class="kicker">Waiting for you</p>
                    <h2>Meet your new best friend</h2>
                </div>
                <a href="{{ route('pets.index') }}" class="btn btn--outline">View all pets <i data-lucide="arrow-right" aria-hidden="true"></i></a>
            </div>

            @if ($featured->isEmpty())
                <div class="empty">
                    <p class="empty__icon">🐾</p>
                    <h3>New friends are getting camera-ready</h3>
                    <p>Check back soon — we're photographing them now.</p>
                </div>
            @else
                <div class="card-grid card-grid--4">
                    @foreach ($featured->take(4) as $pet)
                        @include('partials.pet-card')
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ---- How it works --------------------------------------------------- --}}
    <section class="band" id="how-it-works">
        <div class="shell">
            <div class="band-head band-head--center">
                <div>
                    <p class="kicker kicker--center">Four easy steps</p>
                    <h2>Adoption is simple</h2>
                </div>
            </div>

            <div class="steps-grid">
                <svg class="steps-grid__line" viewBox="0 0 600 20" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0 10C120 -6 200 26 300 10s180-16 300 0" fill="none" stroke="currentColor"
                          stroke-width="2.5" stroke-dasharray="2 9" stroke-linecap="round"/>
                </svg>
                @foreach ([
                    ['butter', 'search', 'Search &amp; connect', 'Find pets that match your lifestyle and preferences.'],
                    ['sky', 'message-circle', 'Ask about them', 'Send your questions — no commitment to adopt.'],
                    ['blush', 'users', 'Meet &amp; bond', 'Meet your potential match and fall in love.'],
                    ['sage', 'heart', 'Adopt &amp; thrive', 'Take them home and start your journey together.'],
                ] as [$tint, $icon, $title, $copy])
                    <article>
                        <span class="steps-grid__ring steps-grid__ring--{{ $tint }}" aria-hidden="true">
                            <i data-lucide="{{ $icon }}"></i>
                        </span>
                        <h3>{!! $title !!}</h3>
                        <p>{{ $copy }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---- In loving memory ----------------------------------------------- --}}
    <section class="band band--raised" id="memorials">
        <div class="shell">
            <div class="stories">
                <div class="stories-grid">
                    <div class="stories__intro">
                        <p class="kicker">In loving memory</p>
                        <h2>A place to<br>remember them</h2>
                        <p>
                            The companions we lose never really leave us. Create a lasting
                            tribute to a beloved pet, light a candle, and share the memories
                            that live on.
                        </p>
                        <a href="{{ route('memorials.index') }}" class="btn btn--accent">
                            Visit the memorials <i data-lucide="arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>

                    <div class="story-photos">
                        <span class="washi" aria-hidden="true"></span>
                        @forelse ($memorials->take(3) as $memorial)
                            <img src="{{ $memorial->photoUrl() }}" alt="{{ $memorial->pet_name }}, in loving memory" loading="lazy">
                        @empty
                            <div class="story-photos__placeholder"><i data-lucide="paw-print" aria-hidden="true"></i></div>
                        @endforelse
                    </div>

                    <blockquote class="quote-card">
                        <div class="quote-card__mark" aria-hidden="true">&ldquo;</div>
                        <p>They were only with us a while, but they left paw prints on our hearts that will never fade.</p>
                        <footer>— In memory of every good companion</footer>
                    </blockquote>
                </div>
            </div>
        </div>
    </section>

    {{-- ---- Guides --------------------------------------------------------- --}}
    <section class="band" id="guides">
        <div class="shell">
            <div class="band-head">
                <div>
                    <p class="kicker">More than adoption</p>
                    <h2>Guides &amp; resources</h2>
                    <p class="lede">Everything you need to give your pet the best life.</p>
                </div>
                <a href="{{ route('services.index') }}" class="btn btn--outline">Explore pet care <i data-lucide="arrow-right" aria-hidden="true"></i></a>
            </div>

            <div class="guide-grid">
                @foreach ([
                    ['boarding', 'sage', 'book-open', 'New pet parent guide', 'Everyday care for a happy start.'],
                    ['house-sitting', 'sky', 'house', 'Prepare your home', 'Make your space safe and pet-friendly.'],
                    ['training', 'blush', 'graduation-cap', 'Training &amp; behaviour', 'Build good habits and a stronger bond.'],
                    ['grooming', 'butter', 'heart-pulse', 'Health &amp; nutrition', 'Support a long and healthy life.'],
                ] as [$slug, $tint, $icon, $title, $copy])
                    <a href="{{ route('services.index', ['category' => $slug]) }}" class="guide-card guide-card--{{ $tint }}">
                        <span class="glyph" aria-hidden="true"><i data-lucide="{{ $icon }}"></i></span>
                        <span>
                            <h3>{!! $title !!}</h3>
                            <p>{{ $copy }}</p>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

@endsection
