@extends('layouts.app')

@section('title', 'Two Fat Cats — Find your new best friend')

@section('content')
    {{-- ---- Hero ---------------------------------------------------------- --}}
    <section class="hero">
        <div class="shell hero__grid">
            <div>
                <p class="kicker">Adopt · Love · Thrive</p>
                <h1>Find the companion who changes <em>everything</em>.</h1>
                <p class="lede">
                    Meet cats and dogs waiting for a second chance, and book trusted local
                    sitters to look after them once they're home.
                </p>

                <div class="hero__actions">
                    <a href="{{ route('pets.index') }}" class="btn btn--accent">Adopt a pet <i data-lucide="paw-print" aria-hidden="true"></i></a>
                    <a href="{{ route('services.index') }}" class="btn btn--outline">Find pet care <i data-lucide="arrow-right" aria-hidden="true"></i></a>
                </div>

                <div class="hero__stats">
                    <div><strong>Local</strong><span>Shelters &amp; rescues near you</span></div>
                    <div><strong>Verified</strong><span>Every listing checked</span></div>
                    <div><strong>Free</strong><span>No fees to adopt or browse</span></div>
                </div>
            </div>

            <div class="hero__visual">
                <span class="hero__badge"><i data-lucide="shield-check" aria-hidden="true"></i> Verified shelters</span>
                <div class="hero__frame">
                    <img src="{{ asset('images/two-fat-cats-hero.png') }}"
                         alt="A golden retriever and a tabby cat sitting together"
                         fetchpriority="high">
                </div>
                <figure class="hero__note">
                    <p>“Best decision, ever!”</p>
                    <footer>Mia &amp; Milo</footer>
                </figure>
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
                <article>
                    <span class="n">01</span>
                    <h3>Search &amp; connect</h3>
                    <p>Filter by species, age, size and temperament to find pets that suit your home.</p>
                </article>
                <article>
                    <span class="n">02</span>
                    <h3>Apply to adopt</h3>
                    <p>Send one application. The shelter reads it and gets back to you directly.</p>
                </article>
                <article>
                    <span class="n">03</span>
                    <h3>Meet &amp; bond</h3>
                    <p>Visit in person, spend time together, and make sure it's the right match.</p>
                </article>
                <article>
                    <span class="n">04</span>
                    <h3>Adopt &amp; thrive</h3>
                    <p>Take them home — and book a sitter here whenever you need a hand.</p>
                </article>
            </div>
        </div>
    </section>

    {{-- ---- Stories -------------------------------------------------------- --}}
    <section class="band band--raised" id="stories">
        <div class="shell story">
            <div class="story__gallery">
                @php($withPhotos = $featured->filter(fn ($pet) => $pet->primaryPhoto()))
                @forelse ($withPhotos->take(2) as $pet)
                    <img src="{{ $pet->primaryPhoto()->url() }}" alt="{{ $pet->name }}, an adoptable pet" loading="lazy">
                @empty
                    <div class="story__placeholder">🐾</div>
                @endforelse
            </div>

            <div>
                <p class="kicker">A new beginning</p>
                <h2>Adoption stories,<br>forever homes</h2>
                <p class="lede">
                    Every adoption is the start of something wonderful — for the pet, and
                    just as often for the person who took them in.
                </p>
                <blockquote class="quote">
                    <p>Finding the right companion was the best decision we made. We didn't just change a life; ours changed too.</p>
                    <footer>Laria &amp; Max — adopted March 2026</footer>
                </blockquote>
                <a href="{{ route('pets.index') }}" class="btn btn--primary" style="margin-top:2rem">
                    Meet your match <i data-lucide="arrow-right" aria-hidden="true"></i>
                </a>
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
                    ['boarding', 'sage', '🐕', 'New pet parent guide', 'Everyday care for a happy start.'],
                    ['house-sitting', 'sky', '🏠', 'Prepare your home', 'Make your space safe and pet-friendly.'],
                    ['training', 'blush', '🧶', 'Training &amp; behaviour', 'Build good habits and a stronger bond.'],
                    ['grooming', 'butter', '🥣', 'Health &amp; nutrition', 'Support a long and healthy life.'],
                ] as [$slug, $tint, $glyph, $title, $copy])
                    <a href="{{ route('services.index', ['category' => $slug]) }}" class="guide-card guide-card--{{ $tint }}">
                        <span class="glyph" aria-hidden="true">{{ $glyph }}</span>
                        <span>
                            <h3>{!! $title !!}</h3>
                            <p>{{ $copy }}</p>
                        </span>
                        <span class="guide-card__go" aria-hidden="true"><i data-lucide="arrow-right"></i></span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---- Reviews -------------------------------------------------------- --}}
    <section class="band band--forest">
        <div class="shell">
            <div class="band-head">
                <div>
                    <p class="kicker">Loved by pets and people</p>
                    <h2>Real stories from our<br>growing community</h2>
                </div>
            </div>

            <div class="reviews">
                @foreach ([
                    ['The whole journey felt clear, caring, and stress-free.', 'Mia &amp; Milo'],
                    ['We found our best friend and felt supported at every step.', 'Amir &amp; Luna'],
                    ['Simple to use, warm, and genuinely focused on the pets.', 'Jo &amp; Bean'],
                ] as [$quote, $who])
                    <blockquote class="review">
                        <div class="stars" aria-label="Rated 5 out of 5">★★★★★</div>
                        <p>“{{ $quote }}”</p>
                        <footer>{!! $who !!}</footer>
                    </blockquote>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---- Closing CTA ---------------------------------------------------- --}}
    <section class="band">
        <div class="shell">
            <div class="cta-strip">
                <div>
                    <h2>Ready to meet your match?</h2>
                    <p>There's a new best friend waiting to say hello.</p>
                </div>
                <div class="hero__actions" style="margin-top:0">
                    <a href="{{ route('pets.index') }}" class="btn btn--accent">Find a pet <i data-lucide="paw-print" aria-hidden="true"></i></a>
                    <a href="{{ route('provider.onboarding') }}" class="btn btn--outline">Become a sitter</a>
                </div>
            </div>
        </div>
    </section>
@endsection
