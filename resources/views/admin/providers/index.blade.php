@extends('layouts.app')

@section('title', 'Sitters — Admin')

@section('content')
    <div class="shell-wide page">
        @include('admin.partials.nav')

        <div class="page-head" style="margin-top:1.5rem">
            <div>
                <p class="kicker">Back of house</p>
                <h1>Sitters</h1>
            </div>
        </div>

        <div class="filter-bar" style="margin-top:1.25rem">
            @foreach (['pending' => 'Awaiting approval', 'approved' => 'Approved', 'suspended' => 'Not listed', 'draft' => 'Drafts', 'all' => 'All'] as $key => $label)
                <a href="{{ route('admin.providers.index', ['status' => $key]) }}"
                   @class(['chip', 'is-on' => $filter === $key])>
                    {{ $label }}
                    @if ($key !== 'all' && ($counts[$key] ?? 0))
                        <span class="chip__count">{{ $counts[$key] }}</span>
                    @endif
                </a>
            @endforeach

            <form method="GET" action="{{ route('admin.providers.index') }}" class="filter-bar__search">
                <input type="hidden" name="status" value="{{ $filter }}">
                <input type="search" name="q" class="input input--sm" value="{{ request('q') }}"
                       placeholder="Search sitters" aria-label="Search sitters">
            </form>
        </div>

        <div class="stack" style="margin-top:1.5rem">
            @forelse ($providers as $provider)
                <article class="panel listing-row">
                    <div class="listing-row__media">
                        @if ($photo = $provider->photos->first())
                            <img src="{{ $photo->url() }}" alt="" loading="lazy">
                        @else
                            <div class="listing-row__placeholder" aria-hidden="true"><i data-lucide="user-round"></i></div>
                        @endif
                    </div>

                    <div class="listing-row__body">
                        <h2>{{ $provider->user?->name ?? 'Unknown' }}</h2>
                        <p class="field-hint">
                            {{ $provider->headline }} · {{ $provider->locationLabel() }}
                        </p>
                        <p style="margin-top:.5rem">
                            <span class="badge badge--{{ $provider->status === 'pending' ? 'submitted' : ($provider->status === 'approved' ? 'approved' : 'rejected') }}">
                                {{ $provider->statusLabel() }}
                            </span>
                            <span class="badge">{{ $provider->services->count() }} {{ Str::plural('service', $provider->services->count()) }}</span>
                            @if ($provider->bookings_count)
                                <span class="badge">{{ $provider->bookings_count }} {{ Str::plural('booking', $provider->bookings_count) }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="listing-row__actions">
                        <a href="{{ route('admin.providers.show', $provider) }}" class="btn btn--outline btn--sm">
                            {{ $provider->status === 'pending' ? 'Review' : 'Open' }}
                        </a>
                    </div>
                </article>
            @empty
                <div class="empty">
                    <p><strong>Nothing here.</strong></p>
                    <p class="field-hint">No sitters match this filter.</p>
                </div>
            @endforelse
        </div>

        <div style="margin-top:1.5rem">{{ $providers->links() }}</div>
    </div>
@endsection
