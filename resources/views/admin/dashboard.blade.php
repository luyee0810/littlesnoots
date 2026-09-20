@extends('layouts.app')

@section('title', 'Admin — Little Snoots')

@section('content')
    <div class="shell-mid page">
        @include('admin.partials.nav')

        <div class="page-head" style="margin-top:1.5rem">
            <div>
                <p class="kicker">Back of house</p>
                <h1>Overview</h1>
            </div>
        </div>

        <div class="stat-row" style="margin-top:1.5rem">
            <a href="{{ route('admin.pets.index', ['status' => 'submitted']) }}"
               @class(['stat', 'stat--attention' => $awaitingReview > 0])>
                <span class="stat__n">{{ $awaitingReview }}</span>
                <span class="stat__label">Listings awaiting review</span>
            </a>
            <div class="stat">
                <span class="stat__n">{{ $pendingApplications }}</span>
                <span class="stat__label">Adoption applications pending</span>
            </div>
            <a href="{{ route('admin.providers.index', ['status' => 'pending']) }}"
               @class(['stat', 'stat--attention' => $pendingProviders > 0])>
                <span class="stat__n">{{ $pendingProviders }}</span>
                <span class="stat__label">Sitters awaiting approval</span>
            </a>
            <div class="stat">
                <span class="stat__n">{{ $livePets }}</span>
                <span class="stat__label">Pets live on the site</span>
            </div>
            <div class="stat">
                <span class="stat__n">{{ $totalUsers }}</span>
                <span class="stat__label">Members</span>
            </div>
        </div>

        <section class="panel" style="margin-top:2rem">
            <div class="panel__head">
                <h2>Review queue</h2>
                @if ($awaitingReview > 5)
                    <a href="{{ route('admin.pets.index') }}" class="panel__note">See all {{ $awaitingReview }}</a>
                @endif
            </div>
            <div class="panel__body">
                @forelse ($queue as $pet)
                    <article class="listing-row listing-row--tight">
                        <div class="listing-row__media">
                            @if ($photo = $pet->primaryPhoto())
                                <img src="{{ $photo->url() }}" alt="" loading="lazy">
                            @endif
                        </div>
                        <div class="listing-row__body">
                            <h3>{{ $pet->name }}</h3>
                            <p class="field-hint">
                                {{ $pet->species?->name }} · listed by {{ $pet->lister?->name ?? 'unknown' }}
                                · {{ $pet->updated_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="listing-row__actions">
                            <a href="{{ route('admin.pets.show', $pet) }}" class="btn btn--outline btn--sm">Review</a>
                        </div>
                    </article>
                @empty
                    <p class="field-hint">Nothing waiting. The queue is clear.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
