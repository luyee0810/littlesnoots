@extends('layouts.app')

@section('title', 'Listings — Admin')

@section('content')
    <div class="shell-wide page">
        @include('admin.partials.nav')

        <div class="page-head" style="margin-top:1.5rem">
            <div>
                <p class="kicker">Back of house</p>
                <h1>Listings</h1>
            </div>
        </div>

        <div class="filter-bar" style="margin-top:1.25rem">
            @foreach (['submitted' => 'Awaiting review', 'approved' => 'Approved', 'rejected' => 'Sent back', 'draft' => 'Drafts', 'all' => 'All'] as $key => $label)
                <a href="{{ route('admin.pets.index', ['status' => $key]) }}"
                   @class(['chip', 'is-on' => $filter === $key])>
                    {{ $label }}
                    @if ($key !== 'all' && ($counts[$key] ?? 0))
                        <span class="chip__count">{{ $counts[$key] }}</span>
                    @endif
                </a>
            @endforeach

            <form method="GET" action="{{ route('admin.pets.index') }}" class="filter-bar__search">
                <input type="hidden" name="status" value="{{ $filter }}">
                <input type="search" name="q" class="input input--sm" value="{{ request('q') }}"
                       placeholder="Search by name" aria-label="Search listings by name">
            </form>
        </div>

        <div class="stack" style="margin-top:1.5rem">
            @forelse ($pets as $pet)
                <article class="panel listing-row">
                    <div class="listing-row__media">
                        @if ($photo = $pet->primaryPhoto())
                            <img src="{{ $photo->url() }}" alt="" loading="lazy">
                        @else
                            <div class="listing-row__placeholder" aria-hidden="true"><i data-lucide="image"></i></div>
                        @endif
                    </div>

                    <div class="listing-row__body">
                        <h2>{{ $pet->name }}</h2>
                        <p class="field-hint">
                            {{ $pet->species?->name }} ·
                            {{ $pet->organization?->name ?? 'Individual rehomer' }} ·
                            listed by {{ $pet->lister?->name ?? 'unknown' }}
                        </p>
                        <p style="margin-top:.5rem">
                            <span class="badge badge--{{ $pet->review_status }}">{{ $pet->reviewLabel() }}</span>
                            @if ($pet->applications_count)
                                <span class="badge">{{ $pet->applications_count }} {{ Str::plural('application', $pet->applications_count) }}</span>
                            @endif
                            <span class="field-hint">· updated {{ $pet->updated_at->diffForHumans() }}</span>
                        </p>
                    </div>

                    <div class="listing-row__actions">
                        <a href="{{ route('admin.pets.show', $pet) }}" class="btn btn--outline btn--sm">
                            {{ $pet->review_status === 'submitted' ? 'Review' : 'Open' }}
                        </a>
                    </div>
                </article>
            @empty
                <div class="empty">
                    <p><strong>Nothing here.</strong></p>
                    <p class="field-hint">No listings match this filter.</p>
                </div>
            @endforelse
        </div>

        <div style="margin-top:1.5rem">{{ $pets->links() }}</div>
    </div>
@endsection
