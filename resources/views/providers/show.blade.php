@extends('layouts.app')

@section('title', $provider->user->name.' — Pet services — Two Fat Cats')

@php
    $photos = $provider->photos;
    $services = $provider->services->where('is_active', true);
    $badges = collect([
        $provider->verified_email_at ? 'Email verified' : null,
        $provider->has_insurance ? 'Insured' : null,
        $provider->is_smoke_free ? 'Smoke-free home' : null,
        $provider->has_fenced_yard ? 'Fenced yard' : null,
    ])->filter();
@endphp

@section('content')
    <div class="shell page">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('services.index') }}">Pet care</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">{{ $provider->user->name }}</span>
        </nav>

        @if (! $provider->isLive())
            <div class="alert alert--warn" style="margin-top:1.25rem">
                This profile isn’t published yet — only you can see it.
            </div>
        @endif

        <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_22rem]" style="margin-top:1.5rem">
            {{-- ---- Left: the profile ---------------------------------------- --}}
            <div>
                @if ($photos->isNotEmpty())
                    <div class="grid gap-3 sm:grid-cols-3">
                        <img src="{{ $photos->first()->url }}"
                             alt="{{ $photos->first()->caption ?? $provider->user->name }}"
                             class="aspect-[16/10] w-full object-cover sm:col-span-2"
                             style="border-radius:var(--r-md)">
                        <div class="grid gap-3 sm:grid-rows-2">
                            @foreach ($photos->skip(1)->take(2) as $photo)
                                <img src="{{ $photo->url }}" alt="{{ $photo->caption ?? '' }}" loading="lazy"
                                     class="aspect-[4/3] w-full object-cover" style="border-radius:var(--r-md)">
                            @endforeach
                        </div>
                    </div>
                @endif

                <div style="margin-top:2rem">
                    <h1 style="font-size:clamp(2.2rem,4.5vw,3rem)">{{ $provider->user->name }}</h1>
                    <p class="lede" style="margin-top:.6rem">{{ $provider->headline }}</p>

                    <div class="meta-inline" style="margin-top:1rem">
                        <span class="listing__place" style="margin-top:0">
                            <i data-lucide="map-pin" aria-hidden="true"></i>{{ $provider->locationLabel() }}
                        </span>
                        @if ($provider->reviews_count > 0)
                            <span>
                                <i data-lucide="star" aria-hidden="true"
                                   style="display:inline-block;width:14px;height:14px;vertical-align:-2px;fill:currentColor;color:var(--ochre)"></i>
                                {{ number_format($provider->rating_avg, 1) }} ({{ $provider->reviews_count }} reviews)
                            </span>
                        @endif
                        @if ($provider->years_experience > 0)
                            <span>{{ $provider->years_experience }} {{ Str::plural('year', $provider->years_experience) }} experience</span>
                        @endif
                        @if ($provider->bookings_count > 0)
                            <span>{{ $provider->bookings_count }} bookings completed</span>
                        @endif
                    </div>

                    @if ($badges->isNotEmpty())
                        <div class="chip-row" style="margin-top:1.25rem">
                            @foreach ($badges as $badge)
                                <span class="chip chip--sage">
                                    <i data-lucide="check" aria-hidden="true" style="width:13px;height:13px"></i>{{ $badge }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($provider->bio)
                    <section class="section-gap">
                        <h2 style="font-size:1.5rem">About</h2>
                        <p class="prose" style="margin-top:.85rem">{{ $provider->bio }}</p>
                    </section>
                @endif

                <section class="section-gap">
                    <h2 style="font-size:1.5rem">Services &amp; rates</h2>
                    <div class="panel rows" style="margin-top:1rem">
                        @forelse ($services as $service)
                            <div class="row" style="align-items:flex-start">
                                <div style="min-width:0">
                                    <h3 style="font-size:1.05rem">
                                        <span aria-hidden="true">{{ $service->category->icon }}</span> {{ $service->label() }}
                                    </h3>
                                    @if ($service->description)
                                        <p class="meta" style="margin-top:.35rem">{{ $service->description }}</p>
                                    @endif
                                    <p class="meta" style="margin-top:.35rem;font-size:.78rem">
                                        Up to {{ $service->max_pets }} {{ Str::plural('pet', $service->max_pets) }}
                                        @if ($service->additional_pet_price)
                                            · +RM {{ number_format($service->additional_pet_price, 0) }} per extra pet
                                        @endif
                                    </p>
                                </div>
                                <div style="text-align:right;flex:0 0 auto">
                                    <div class="price">RM {{ number_format($service->price, 0) }}</div>
                                    <div class="label">{{ $service->category->priceSuffix() }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="row"><p class="meta">This sitter hasn’t listed any services yet.</p></div>
                        @endforelse
                    </div>
                </section>

                <section class="section-gap">
                    <h2 style="font-size:1.5rem">Good to know</h2>
                    <div class="card card-pad" style="margin-top:1rem">
                        <dl class="dl dl--cols">
                            @if ($provider->home_type)
                                <div><dt>Home</dt><dd class="capitalize">{{ $provider->home_type }}</dd></div>
                            @endif
                            <div><dt>Max pets</dt><dd>{{ $provider->max_pets_per_booking }} per booking</dd></div>
                            <div><dt>Has own pets</dt><dd>{{ $provider->has_own_pets ? 'Yes' : 'No' }}</dd></div>
                            <div><dt>Travels up to</dt><dd>{{ $provider->service_radius_km }} km</dd></div>
                            @if ($provider->accepts_species)
                                <div><dt>Accepts</dt><dd class="capitalize">{{ implode(', ', $provider->accepts_species) }}</dd></div>
                            @endif
                            @if ($provider->available_days)
                                <div><dt>Available</dt><dd>{{ implode(', ', $provider->available_days) }}</dd></div>
                            @endif
                        </dl>
                    </div>
                </section>
            </div>

            {{-- ---- Right: booking panel -------------------------------------- --}}
            <aside class="lg:sticky lg:top-24 lg:h-max">
                @include('providers.partials.booking-form', [
                    'provider' => $provider,
                    'services' => $services,
                    'species' => $species,
                    'blockedDates' => $blockedDates,
                ])
            </aside>
        </div>
    </div>
@endsection
