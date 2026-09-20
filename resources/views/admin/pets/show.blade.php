@extends('layouts.app')

@section('title', "Review {$pet->name} — Admin")

@section('content')
    <div class="shell-mid page">
        @include('admin.partials.nav')

        <div class="page-head" style="margin-top:1.5rem">
            <div>
                <p class="kicker"><a href="{{ route('admin.pets.index') }}">← All listings</a></p>
                <h1>{{ $pet->name }}</h1>
                <p class="lede" style="margin-top:.5rem">
                    <span class="badge badge--{{ $pet->review_status }}">{{ $pet->reviewLabel() }}</span>
                    @if ($pet->reviewed_at)
                        <span class="field-hint">
                            · {{ $pet->reviewer?->name }} {{ $pet->reviewed_at->diffForHumans() }}
                        </span>
                    @endif
                </p>
            </div>
        </div>

        @include('listings.partials.errors')

        @if ($pet->photos->isNotEmpty())
            <div class="photo-grid" style="margin-top:1.5rem">
                @foreach ($pet->photos as $photo)
                    <figure class="photo-grid__item">
                        <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" loading="lazy">
                        @if ($photo->is_primary)
                            <figcaption><span class="badge badge--approved">Main</span></figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @else
            <div class="alert alert--bad" style="margin-top:1.5rem">This listing has no photos.</div>
        @endif

        <section class="panel" style="margin-top:1.5rem">
            <div class="panel__head"><h2>The listing</h2></div>
            <div class="panel__body">
                <dl class="detail-grid">
                    <div><dt>Species</dt><dd>{{ $pet->species?->name }}</dd></div>
                    <div><dt>Breed</dt><dd>{{ $pet->breedLabel() }}</dd></div>
                    <div><dt>Age</dt><dd>{{ ucfirst($pet->age_group) }}@if ($pet->age_months) ({{ $pet->age_months }} months)@endif</dd></div>
                    <div><dt>Gender</dt><dd>{{ ucfirst($pet->gender) }}</dd></div>
                    <div><dt>Size</dt><dd>{{ ucfirst($pet->size) }}</dd></div>
                    <div><dt>Status</dt><dd>{{ ucfirst($pet->status) }}</dd></div>
                    <div><dt>Location</dt><dd>{{ $pet->location ?: '—' }}</dd></div>
                    <div><dt>Fee</dt><dd>{{ $pet->adoption_fee ? 'RM '.number_format((float) $pet->adoption_fee, 2) : 'None' }}</dd></div>
                    <div>
                        <dt>Rehomed by</dt>
                        <dd>
                            {{ $pet->organization?->name ?? 'Individual' }}
                            <span class="field-hint">({{ $pet->lister?->name ?? 'unknown' }}, {{ $pet->lister?->email }})</span>
                        </dd>
                    </div>
                </dl>

                <div style="margin-top:1.25rem">
                    <h3>Their story</h3>
                    <p style="margin-top:.5rem;white-space:pre-line">{{ $pet->description }}</p>
                </div>
            </div>
        </section>

        <section class="panel" style="margin-top:1.5rem">
            <div class="panel__head">
                <h2>Decision</h2>
                <p class="panel__note">Approving publishes this listing immediately.</p>
            </div>
            <div class="panel__body form-grid">
                @if ($pet->review_status !== 'approved')
                    <form method="POST" action="{{ route('admin.pets.approve', $pet) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn--accent">Approve &amp; publish</button>
                    </form>
                @endif

                <form method="POST"
                      action="{{ $pet->isPublished() ? route('admin.pets.unpublish', $pet) : route('admin.pets.reject', $pet) }}"
                      class="form-grid">
                    @csrf @method('PATCH')
                    <div class="field">
                        <label for="review_notes">
                            {{ $pet->isPublished() ? 'Why are you unpublishing this?' : 'What needs changing?' }}
                        </label>
                        <textarea name="review_notes" id="review_notes" rows="3" class="textarea" required
                                  minlength="10" maxlength="1000"
                                  placeholder="The lister sees this, so be specific — e.g. the photos are too blurry to show the cat.">{{ old('review_notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn--outline">
                        {{ $pet->isPublished() ? 'Unpublish' : 'Send back to lister' }}
                    </button>
                </form>
            </div>
        </section>
    </div>
@endsection
