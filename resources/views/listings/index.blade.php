@extends('layouts.app')

@section('title', 'My listings — Little Snoots')

@section('content')
    <div class="shell-mid page">
        <div class="page-head">
            <div>
                <p class="kicker">Rehoming</p>
                <h1>My listings</h1>
                <p class="lede" style="margin-top:.75rem">Pets you’ve listed for adoption.</p>
            </div>
            <a href="{{ route('listings.create') }}" class="btn btn--accent">
                <i data-lucide="plus" aria-hidden="true"></i> List a pet
            </a>
        </div>

        @if ($pets->isEmpty())
            <div class="empty" style="margin-top:2rem">
                <p><strong>No listings yet.</strong></p>
                <p class="field-hint">
                    Rescued or fostering a pet who needs a home? Listing takes a few minutes,
                    and no shelter is required.
                </p>
                <a href="{{ route('listings.create') }}" class="btn btn--accent" style="margin-top:1rem">List a pet</a>
            </div>
        @else
            <div class="stack" style="margin-top:2rem">
                @foreach ($pets as $pet)
                    <article class="panel listing-row">
                        <div class="listing-row__media">
                            @if ($photo = $pet->primaryPhoto())
                                <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" loading="lazy">
                            @else
                                <div class="listing-row__placeholder" aria-hidden="true">
                                    <i data-lucide="image"></i>
                                </div>
                            @endif
                        </div>

                        <div class="listing-row__body">
                            <h2>{{ $pet->name }}</h2>
                            <p class="field-hint">{{ $pet->breedLabel() }} · {{ ucfirst($pet->age_group) }}</p>

                            <p style="margin-top:.5rem">
                                <span class="badge badge--{{ $pet->review_status }}">{{ $pet->reviewLabel() }}</span>
                                @if ($pet->applications_count)
                                    <a href="{{ route('listings.applications', $pet) }}" class="badge badge--submitted">
                                        {{ $pet->applications_count }} {{ Str::plural('application', $pet->applications_count) }}
                                    </a>
                                @endif
                            </p>

                            @if ($pet->review_status === 'rejected' && $pet->review_notes)
                                <p class="field-hint" style="margin-top:.5rem">{{ $pet->review_notes }}</p>
                            @endif
                        </div>

                        <div class="listing-row__actions">
                            <a href="{{ route('listings.applications', $pet) }}" class="btn btn--outline btn--sm">Applications</a>
                            <a href="{{ route('listings.edit', $pet) }}" class="btn btn--ghost btn--sm">Edit</a>
                            @if ($pet->isPublished())
                                <a href="{{ route('pets.show', $pet) }}" class="btn btn--ghost btn--sm">View</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
