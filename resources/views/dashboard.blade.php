@extends('layouts.app')

@section('title', 'My dashboard — Two Fat Cats')

@section('content')
    <div class="shell-mid page">
        <div class="page-head">
            <div>
                <p class="kicker">Your account</p>
                <h1>Hello, {{ auth()->user()->name }}</h1>
                <p class="lede" style="margin-top:.5rem">
                    {{ auth()->user()->isStaff()
                        ? "Manage the pets you've listed for adoption."
                        : "Track the adoption applications you've submitted." }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if ($providerProfile)
                    <a href="{{ route('provider.bookings.index') }}" class="btn btn--outline btn--sm">Sitter dashboard</a>
                @else
                    <a href="{{ route('provider.onboarding') }}" class="btn btn--outline btn--sm">Become a sitter</a>
                @endif
                <a href="{{ route('pets.index') }}" class="btn btn--accent btn--sm">Browse pets</a>
            </div>
        </div>

        {{-- ---- Service bookings (Phase 2) -------------------------------- --}}
        <section class="section-gap">
            <div class="flex flex-wrap items-baseline justify-between gap-4">
                <p class="label">My service bookings</p>
                <a href="{{ route('services.index') }}" class="link-quiet" style="font-size:.85rem">Find a sitter →</a>
            </div>

            @if ($bookings->isEmpty())
                <div class="empty" style="margin-top:1rem;padding-block:2.5rem">
                    <p class="empty__icon">🏡</p>
                    <h2>No bookings yet</h2>
                    <p>Browse boarding, walking and grooming near you.</p>
                    <a href="{{ route('services.index') }}" class="btn btn--outline btn--sm">Find a sitter</a>
                </div>
            @else
                <div class="panel rows" style="margin-top:1rem">
                    @foreach ($bookings as $booking)
                        <div class="row">
                            <div style="min-width:0">
                                <a href="{{ route('bookings.show', $booking) }}" class="link-draw" style="font-weight:600">
                                    <span aria-hidden="true">{{ $booking->category->icon }}</span>
                                    {{ $booking->category->name }} for {{ $booking->pet_name }}
                                </a>
                                <p class="meta" style="margin-top:.3rem">
                                    {{ $booking->providerProfile->user->name }} · {{ $booking->dateRangeLabel() }}
                                </p>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="price" style="font-size:1rem">{{ $booking->totalLabel() }}</span>
                                <span class="status {{ $booking->statusClasses() }}">{{ $booking->statusLabel() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ---- Adoption ---------------------------------------------------- --}}
        <section class="section-gap">
            <p class="label">
                {{ auth()->user()->isStaff() ? 'Pets I’ve listed' : 'My adoption applications' }}
            </p>

            @isset($listedPets)
                {{-- Rehomer view: pets this member has posted for adoption --}}
                <div class="panel rows" style="margin-top:1rem">
                    @forelse ($listedPets as $pet)
                        @php($photo = $pet->primaryPhoto())
                        @php($applicationCount = $pet->applications()->count())
                        <div class="row">
                            <div class="flex items-center gap-4" style="min-width:0">
                                <div class="thumb">
                                    @if ($photo)
                                        <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" loading="lazy">
                                    @else
                                        <span aria-hidden="true">🐾</span>
                                    @endif
                                </div>
                                <div style="min-width:0">
                                    <a href="{{ route('pets.show', $pet) }}" class="link-draw" style="font-weight:600">{{ $pet->name }}</a>
                                    <p class="meta" style="margin-top:.2rem">{{ $pet->breedLabel() }}</p>
                                    <p class="meta" style="font-size:.78rem">
                                        {{ $applicationCount }} {{ \Illuminate\Support\Str::plural('application', $applicationCount) }}
                                    </p>
                                </div>
                            </div>
                            <span class="status status--idle">{{ $pet->status }}</span>
                        </div>
                    @empty
                        <div class="empty" style="border:0;padding-block:2.5rem">
                            <p class="empty__icon">📋</p>
                            <h2>You haven't listed any pets yet</h2>
                            <p>Listing management is coming soon — you'll be able to post pets right here.</p>
                        </div>
                    @endforelse
                </div>
            @else
                {{-- Adopter view: applications this member has submitted --}}
                <div class="panel rows" style="margin-top:1rem">
                    @forelse ($applications as $application)
                        @php($pet = $application->pet)
                        @php($photo = $pet?->primaryPhoto())
                        <div class="row">
                            <div class="flex items-center gap-4" style="min-width:0">
                                <div class="thumb">
                                    @if ($photo)
                                        <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" loading="lazy">
                                    @else
                                        <span aria-hidden="true">🐾</span>
                                    @endif
                                </div>
                                <div style="min-width:0">
                                    @if ($pet)
                                        <a href="{{ route('pets.show', $pet) }}" class="link-draw" style="font-weight:600">{{ $pet->name }}</a>
                                        <p class="meta" style="margin-top:.2rem">{{ $pet->breedLabel() }}</p>
                                    @else
                                        <p style="font-weight:600">Pet no longer listed</p>
                                    @endif
                                    <p class="meta" style="font-size:.78rem">Applied {{ $application->created_at->format('M j, Y') }}</p>
                                </div>
                            </div>
                            <span @class([
                                'status',
                                'status--warn' => $application->status === 'pending',
                                'status--ok' => $application->status === 'approved',
                                'status--bad' => $application->status === 'rejected',
                                'status--idle' => ! in_array($application->status, ['pending', 'approved', 'rejected'], true),
                            ])>{{ $application->status }}</span>
                        </div>
                    @empty
                        <div class="empty" style="border:0;padding-block:2.5rem">
                            <p class="empty__icon">🐾</p>
                            <h2>You haven't applied for any pets yet</h2>
                            <p>Find your new best friend — it starts with one application.</p>
                            <a href="{{ route('pets.index') }}" class="btn btn--accent btn--sm">Browse adoptable pets</a>
                        </div>
                    @endforelse
                </div>
            @endisset
        </section>
    </div>
@endsection
