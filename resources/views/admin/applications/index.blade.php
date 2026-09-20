@extends('layouts.app')

@section('title', 'Applications — Admin')

@section('content')
    <div class="shell-wide page">
        @include('admin.partials.nav')

        <div class="page-head" style="margin-top:1.5rem">
            <div>
                <p class="kicker">Back of house</p>
                <h1>Adoption applications</h1>
                <p class="lede" style="margin-top:.5rem">
                    Decisions are made on each pet’s own page — this is who’s waiting.
                </p>
            </div>
        </div>

        <div class="filter-bar" style="margin-top:1.25rem">
            @foreach (['open' => 'Open', 'pending' => 'New', 'reviewing' => 'Being considered', 'approved' => 'Approved', 'rejected' => 'Not chosen', 'all' => 'All'] as $key => $label)
                <a href="{{ route('admin.applications.index', ['status' => $key]) }}"
                   @class(['chip', 'is-on' => $filter === $key])>
                    {{ $label }}
                    @if (($counts[$key] ?? 0))
                        <span class="chip__count">{{ $counts[$key] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="stack" style="margin-top:1.5rem">
            @forelse ($applications as $application)
                @php($pet = $application->pet)
                <article class="panel listing-row">
                    <div class="listing-row__media">
                        @if ($pet && ($photo = $pet->primaryPhoto()))
                            <img src="{{ $photo->url() }}" alt="" loading="lazy">
                        @else
                            <div class="listing-row__placeholder" aria-hidden="true"><i data-lucide="image"></i></div>
                        @endif
                    </div>

                    <div class="listing-row__body">
                        <h2>{{ $application->applicant_name }} → {{ $pet?->name ?? 'deleted listing' }}</h2>
                        <p class="field-hint">
                            {{ $application->applicant_email }}
                            @if ($pet?->lister)
                                · handled by {{ $pet->lister->name }}
                            @endif
                            · {{ $application->created_at->diffForHumans() }}
                        </p>
                        <p style="margin-top:.5rem">
                            <span class="badge badge--{{ match ($application->status) {
                                'pending' => 'submitted',
                                'reviewing' => 'draft',
                                'approved' => 'approved',
                                default => 'rejected',
                            } }}">{{ $application->statusLabel() }}</span>
                        </p>
                    </div>

                    <div class="listing-row__actions">
                        @if ($pet)
                            <a href="{{ route('listings.applications', $pet) }}" class="btn btn--outline btn--sm">Open</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="empty">
                    <p><strong>Nothing waiting.</strong></p>
                    <p class="field-hint">No applications match this filter.</p>
                </div>
            @endforelse
        </div>

        <div style="margin-top:1.5rem">{{ $applications->links() }}</div>
    </div>
@endsection
