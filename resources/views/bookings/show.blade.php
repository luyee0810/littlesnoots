@extends('layouts.app')

@section('title', 'Booking '.$booking->reference.' — Two Fat Cats')

@php
    $provider = $booking->providerProfile;
    $isOwner = $booking->user_id === auth()->id();
@endphp

@section('content')
    <div class="shell-mid page">
        <a href="{{ $isOwner ? route('dashboard') : route('provider.bookings.index') }}" class="back-link">
            <i data-lucide="arrow-left" aria-hidden="true"></i> Back to {{ $isOwner ? 'dashboard' : 'bookings' }}
        </a>

        <div class="page-head" style="margin-top:1.5rem">
            <div>
                <p class="kicker">Booking {{ $booking->reference }}</p>
                <h1>{{ $booking->category->name }} for {{ $booking->pet_name }}</h1>
                <p class="meta" style="margin-top:.5rem">Requested {{ $booking->created_at->diffForHumans() }}</p>
            </div>
            <span class="status {{ $booking->statusClasses() }}">{{ $booking->statusLabel() }}</span>
        </div>

        @if ($booking->isAwaitingResponse() && $booking->expires_at)
            <div class="alert alert--warn" style="margin-top:1.5rem">
                Waiting on {{ $provider->user->name }} — this request lapses {{ $booking->expires_at->diffForHumans() }}.
            </div>
        @endif

        @if ($booking->provider_response)
            <div class="card card-pad" style="margin-top:1.5rem">
                <p class="label">{{ $provider->user->name }} replied</p>
                <p class="prose" style="margin-top:.5rem">{{ $booking->provider_response }}</p>
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2" style="margin-top:2rem">
            <section class="panel">
                <div class="panel__head"><h2>The service</h2></div>
                <div class="panel__body" style="padding-block:.5rem">
                    <dl class="dl">
                        <div>
                            <dt>Service</dt>
                            <dd><span aria-hidden="true">{{ $booking->category->icon }}</span> {{ $booking->category->name }}</dd>
                        </div>
                        <div><dt>When</dt><dd>{{ $booking->dateRangeLabel() }}</dd></div>
                        <div>
                            <dt>Duration</dt>
                            <dd>{{ $booking->unit_quantity }} {{ Str::plural($booking->unit_label, $booking->unit_quantity) }}</dd>
                        </div>
                        <div>
                            <dt>Sitter</dt>
                            <dd><a href="{{ route('providers.show', $provider) }}" class="link-quiet">{{ $provider->user->name }}</a></dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="panel">
                <div class="panel__head"><h2>The pet</h2></div>
                <div class="panel__body" style="padding-block:.5rem">
                    <dl class="dl">
                        <div><dt>Name</dt><dd>{{ $booking->pet_name }}</dd></div>
                        @if ($booking->pet_breed)<div><dt>Breed</dt><dd>{{ $booking->pet_breed }}</dd></div>@endif
                        @if ($booking->pet_size)<div><dt>Size</dt><dd class="capitalize">{{ $booking->pet_size }}</dd></div>@endif
                        <div><dt>Number of pets</dt><dd>{{ $booking->pet_count }}</dd></div>
                    </dl>
                    @if ($booking->pet_notes)
                        <p class="meta" style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--rule-faint)">{{ $booking->pet_notes }}</p>
                    @endif
                </div>
            </section>
        </div>

        <section class="panel" style="margin-top:1.25rem">
            <div class="panel__head"><h2>Agreed price</h2></div>
            <div class="panel__body" style="padding-block:.5rem">
                <dl class="dl">
                    <div>
                        <dt>RM {{ number_format($booking->unit_price, 2) }} × {{ $booking->unit_quantity }} {{ Str::plural($booking->unit_label, $booking->unit_quantity) }}</dt>
                        <dd>RM {{ number_format($booking->unit_price * $booking->unit_quantity, 2) }}</dd>
                    </div>
                    @if ($booking->additional_pet_price && $booking->pet_count > 1)
                        <div>
                            <dt>{{ $booking->pet_count - 1 }} extra {{ Str::plural('pet', $booking->pet_count - 1) }}</dt>
                            <dd>RM {{ number_format($booking->total - $booking->unit_price * $booking->unit_quantity, 2) }}</dd>
                        </div>
                    @endif
                    <div style="border-top:1px solid var(--rule);padding-top:1rem">
                        <dt style="font-size:.8rem;color:var(--ink)">Total</dt>
                        <dd class="price" style="font-size:1.35rem">{{ $booking->totalLabel() }}</dd>
                    </div>
                </dl>
                <p class="field-hint" style="margin-top:1rem">
                    Payment is arranged directly between you and the sitter — Two Fat Cats doesn’t collect it.
                </p>
            </div>
        </section>

        @if ($booking->message)
            <section class="panel" style="margin-top:1.25rem">
                <div class="panel__head"><h2>Your message</h2></div>
                <div class="panel__body"><p class="prose">{{ $booking->message }}</p></div>
            </section>
        @endif

        @can('cancel', $booking)
            <div style="margin-top:2rem">
                <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                      onsubmit="return confirm('Cancel this booking?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn--outline btn--danger btn--sm">Cancel booking</button>
                </form>
            </div>
        @endcan
    </div>
@endsection
