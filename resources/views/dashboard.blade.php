@extends('layouts.app')

@section('title', 'My dashboard — Little Snoots')

@section('content')
    <div class="shell-mid page">
        <div class="page-head">
            <div>
                <p class="kicker">Your account</p>
                <h1>Hello, {{ auth()->user()->name }}</h1>
                <p class="lede" style="margin-top:.5rem">
                    {{ auth()->user()->isStaff()
                        ? "Manage the pets you've listed for adoption."
                        : "Track the pets you've asked about." }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if ($providerProfile)
                    <a href="{{ route('provider.bookings.index') }}" class="btn btn--outline btn--sm">Provider dashboard</a>
                @else
                    <a href="{{ route('provider.onboarding') }}" class="btn btn--outline btn--sm">Offer pet services</a>
                @endif
                <a href="{{ route('listings.index') }}" class="btn btn--outline btn--sm">
                    {{ $hasListings ? 'My listings' : 'List a pet' }}
                </a>
                <a href="{{ route('pets.index') }}" class="btn btn--accent btn--sm">Browse pets</a>
            </div>
        </div>

        {{-- ---- Anything waiting on me ------------------------------------- --}}
        @if ($unreadTotal || $awaitingReview->isNotEmpty())
            <section class="section-gap">
                <div class="panel rows">
                    @if ($unreadTotal)
                        <div class="row">
                            <div>
                                <strong>{{ $unreadTotal }} unread {{ Str::plural('message', $unreadTotal) }}</strong>
                                <p class="meta">From your providers.</p>
                            </div>
                        </div>
                    @endif
                    @foreach ($awaitingReview as $booking)
                        <div class="row">
                            <div style="min-width:0">
                                <strong>How was {{ $booking->providerProfile->user->name }}?</strong>
                                <p class="meta">{{ $booking->category->name }} for {{ $booking->pet_name }} · {{ $booking->dateRangeLabel() }}</p>
                            </div>
                            <a href="{{ route('bookings.show', $booking) }}" class="btn btn--accent btn--sm">Leave a review</a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ---- Upcoming bookings ------------------------------------------- --}}
        <section class="section-gap">
            <div class="flex flex-wrap items-baseline justify-between gap-4">
                <p class="label">Upcoming bookings</p>
                <a href="{{ route('services.index') }}" class="link-quiet" style="font-size:.85rem">Find pet care →</a>
            </div>

            @if ($upcoming->isEmpty())
                <div class="empty" style="margin-top:1rem;padding-block:2.5rem">
                    <p class="empty__icon">🏡</p>
                    <h2>Nothing booked right now</h2>
                    <p>Browse boarding, walking and grooming near you.</p>
                    <a href="{{ route('services.index') }}" class="btn btn--outline btn--sm">Find pet care</a>
                </div>
            @else
                <div class="panel rows" style="margin-top:1rem">
                    @foreach ($upcoming as $booking)
                        @include('partials.booking-row', ['booking' => $booking])
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ---- Past bookings ----------------------------------------------- --}}
        @if ($past->isNotEmpty())
            <section class="section-gap">
                <p class="label">Past bookings</p>
                <div class="panel rows" style="margin-top:1rem">
                    @foreach ($past as $booking)
                        @include('partials.booking-row', ['booking' => $booking])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ---- Pets I've asked about ---------------------------------------- --}}
        <section class="section-gap">
            <div class="flex flex-wrap items-baseline justify-between gap-4">
                <p class="label">Pets I’ve asked about</p>
                <a href="{{ route('listings.create') }}" class="link-quiet" style="font-size:.85rem">List a pet →</a>
            </div>

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
                                <p class="meta" style="font-size:.78rem">Asked {{ $application->created_at->format('M j, Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span @class([
                                'status',
                                'status--warn' => $application->status === 'pending',
                                'status--ok' => $application->status === 'approved',
                                'status--bad' => $application->status === 'rejected',
                                'status--idle' => ! in_array($application->status, ['pending', 'approved', 'rejected'], true),
                            ])>{{ $application->statusLabel() }}</span>

                            @can('withdraw', $application)
                                <form method="POST" action="{{ route('applications.withdraw', $application) }}"
                                      onsubmit="return confirm('Withdraw your application?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn--ghost btn--sm">Withdraw</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="empty" style="border:0;padding-block:2.5rem">
                        <p class="empty__icon">🐾</p>
                        <h2>You haven't asked about any pets yet</h2>
                        <p>Find your new best friend — it starts with a question.</p>
                        <a href="{{ route('pets.index') }}" class="btn btn--accent btn--sm">Browse adoptable pets</a>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
