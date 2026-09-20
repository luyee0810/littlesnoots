@extends('layouts.app')

@section('title', 'Booking requests — Little Snoots')

@section('content')
    <div class="shell-mid page">
        @include('provider.partials.nav', ['active' => 'bookings'])
        @include('provider.partials.status-banner')

        <div class="page-head" style="margin-top:2rem">
            <div><h1>Booking requests</h1></div>
        </div>

        {{-- ---- Pending: the bit that needs action ------------------------ --}}
        <section style="margin-top:2.5rem">
            <p class="label">Needs your response ({{ $pending->count() }})</p>

            @forelse ($pending as $booking)
                <article class="card card-pad" style="margin-top:1rem;border-color:color-mix(in srgb, var(--ochre) 45%, transparent);background:color-mix(in srgb, var(--butter) 30%, #fff)">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div style="min-width:0">
                            <h2 style="font-size:1.15rem">
                                <i class="svc-icon" data-lucide="{{ $booking->category->lucideIcon() }}" aria-hidden="true"></i>
                                {{ $booking->category->name }} for {{ $booking->pet_name }}
                            </h2>
                            <p class="meta" style="margin-top:.4rem">
                                {{ $booking->owner_name }} · {{ $booking->dateRangeLabel() }} ·
                                {{ $booking->unit_quantity }} {{ Str::plural($booking->unit_label, $booking->unit_quantity) }}
                            </p>
                            @if ($booking->message)
                                <p class="prose" style="margin-top:.75rem;font-style:italic">“{{ $booking->message }}”</p>
                            @endif
                            @if ($booking->expires_at)
                                <p class="label" style="margin-top:.75rem;color:var(--warn-ink)">
                                    Lapses {{ $booking->expires_at->diffForHumans() }}
                                </p>
                            @endif
                        </div>
                        <div style="text-align:right;flex:0 0 auto">
                            <div class="price">{{ $booking->totalLabel() }}</div>
                            <a href="{{ route('bookings.show', $booking) }}" class="label link-quiet">{{ $booking->reference }}</a>
                            @if ($booking->unread_count ?? 0)
                                <a href="{{ route('bookings.show', $booking) }}#messages" class="unread-dot">
                                    {{ $booking->unread_count }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3" style="margin-top:1.5rem">
                        <form method="POST" action="{{ route('provider.bookings.accept', $booking) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn--primary btn--sm">
                                <i data-lucide="check" aria-hidden="true"></i> Accept
                            </button>
                        </form>
                        <form method="POST" action="{{ route('provider.bookings.decline', $booking) }}"
                              onsubmit="return confirm('Decline this request?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn--outline btn--danger btn--sm">Decline</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="empty" style="margin-top:1rem;padding-block:2.5rem">
                    <p class="empty__icon">☕</p>
                    <h2>Nothing waiting on you</h2>
                    <p>New requests will show up here.</p>
                </div>
            @endforelse
        </section>

        {{-- ---- Everything else ------------------------------------------- --}}
        <section class="section-gap">
            <p class="label">All bookings</p>

            @if ($bookings->isEmpty())
                <div class="empty" style="margin-top:1rem;padding-block:2.5rem">
                    <p>No past bookings yet.</p>
                </div>
            @else
                <div class="panel rows" style="margin-top:1rem">
                    @foreach ($bookings as $booking)
                        <div class="row">
                            <div style="min-width:0">
                                <a href="{{ route('bookings.show', $booking) }}" class="link-draw" style="font-weight:600">
                                    {{ $booking->category->name }} for {{ $booking->pet_name }}
                                </a>
                                <p class="meta" style="margin-top:.3rem">
                                    {{ $booking->owner_name }} · {{ $booking->dateRangeLabel() }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($booking->unread_count ?? 0)
                                    <a href="{{ route('bookings.show', $booking) }}#messages" class="unread-dot"
                                       title="{{ $booking->unread_count }} unread {{ Str::plural('message', $booking->unread_count) }}">
                                        {{ $booking->unread_count }}
                                    </a>
                                @endif
                                <span class="status {{ $booking->statusClasses() }}">{{ $booking->statusLabel() }}</span>
                                @can('complete', $booking)
                                    <form method="POST" action="{{ route('provider.bookings.complete', $booking) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn--outline btn--xs">Mark completed</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
