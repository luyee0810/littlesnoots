@extends('layouts.app')

@section('title', 'Pet services — Little Snoots')

@section('content')
    {{-- ---- Search header -------------------------------------------------- --}}
    <section class="band band--raised" style="padding-block:4rem 3.5rem">
        <div class="shell">
            <p class="kicker">Pet care</p>
            <h1 style="font-size:clamp(2.2rem,4.5vw,3.4rem);max-width:16ch">Trusted pet care, close to home</h1>
            <p class="lede" style="margin-top:1rem">
                Boarding, walking, grooming and more — from sitters across the Klang Valley
                and beyond. Browse profiles, then book the person you like.
            </p>

            <div style="margin-top:2rem">
                @include('partials.service-search', ['categories' => $categories, 'filters' => $filters])
            </div>
        </div>
    </section>

    <div class="shell page">
        {{-- ---- Categories ------------------------------------------------- --}}
        <div class="band-head" style="margin-bottom:2rem">
            <div>
                <p class="kicker">Browse by need</p>
                <h2>What are you looking for?</h2>
            </div>
        </div>

        <div class="card-grid">
            @foreach ($categories as $category)
                <a href="{{ route('services.show', $category) }}" class="tile">
                    <span class="tile__icon {{ $category->iconColorClass() }}" aria-hidden="true"><i data-lucide="{{ $category->lucideIcon() }}"></i></span>
                    <h3>{{ $category->name }}</h3>
                    <p>{{ $category->tagline }}</p>
                    <span class="tile__foot">
                        {{ $category->provider_services_count }}
                        {{ Str::plural('sitter', $category->provider_services_count) }}
                        · priced {{ $category->priceSuffix() }}
                    </span>
                </a>
            @endforeach
        </div>

        {{-- ---- Featured sitters ------------------------------------------- --}}
        @if ($featured->isNotEmpty())
            <div class="band-head section-gap" style="margin-bottom:2rem">
                <div>
                    <p class="kicker">Highly rated</p>
                    <h2>Top rated sitters</h2>
                </div>
            </div>

            <div class="card-grid">
                @foreach ($featured as $provider)
                    @include('partials.provider-card', ['provider' => $provider, 'category' => null])
                @endforeach
            </div>
        @endif

        {{-- ---- Become a sitter -------------------------------------------- --}}
        <div class="cta-strip section-gap">
            <div>
                <p class="kicker">Earn doing what you love</p>
                <h2>Good with animals?</h2>
                <p>List your services and start taking bookings from pet owners near you.</p>
            </div>
            <a href="{{ route('provider.onboarding') }}" class="btn btn--accent">
                Become a sitter <i data-lucide="arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
@endsection
