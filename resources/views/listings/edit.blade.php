@extends('layouts.app')

@section('title', "Edit {$pet->name}'s listing — Little Snoots")

@section('content')
    <div class="shell-mid page">
        <div class="page-head">
            <div>
                <a href="{{ route('listings.index') }}" class="back-link">
                    <i data-lucide="arrow-left" aria-hidden="true"></i> My listings
                </a>
                <h1>{{ $pet->name }}’s listing</h1>
                <p class="lede" style="margin-top:.75rem">
                    <span class="badge badge--{{ $pet->review_status }}">{{ $pet->reviewLabel() }}</span>
                    @if ($pet->isPublished())
                        · <a href="{{ route('pets.show', $pet) }}">View public page</a>
                    @endif
                </p>
            </div>
        </div>

        @if ($pet->review_status === 'rejected' && $pet->review_notes)
            <div class="alert alert--bad" style="margin-top:1.5rem">
                <strong>Changes needed before this can go live</strong>
                <p style="margin-top:.35rem">{{ $pet->review_notes }}</p>
            </div>
        @endif

        @include('listings.partials.errors')

        {{-- Photos sit outside the main form: uploads and deletions apply
             immediately, so an unsaved edit can't lose them. --}}
        <section class="panel" style="margin-top:2rem">
            <div class="panel__head">
                <h2>Photos</h2>
                <p class="panel__note">{{ $pet->photos->count() }} of 8 · the main photo leads the listing</p>
            </div>
            <div class="panel__body form-grid">
                @if ($pet->photos->isNotEmpty())
                    <div class="photo-grid">
                        @foreach ($pet->photos as $photo)
                            <figure class="photo-grid__item">
                                <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" loading="lazy">
                                <figcaption>
                                    @if ($photo->is_primary)
                                        <span class="badge badge--approved">Main</span>
                                    @else
                                        <form method="POST" action="{{ route('listings.photos.primary', [$pet, $photo]) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn--outline btn--sm">Make main</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('listings.photos.destroy', [$pet, $photo]) }}"
                                          onsubmit="return confirm('Remove this photo?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn--ghost btn--sm">Remove</button>
                                    </form>
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                @else
                    <p class="field-hint">No photos yet. A listing needs at least one before review.</p>
                @endif

                <form method="POST" action="{{ route('listings.update', $pet) }}" enctype="multipart/form-data"
                      class="field">
                    @csrf @method('PUT')
                    @include('listings.partials.hidden-fields')
                    <label for="photos">Add photos</label>
                    <input type="file" name="photos[]" id="photos" class="input" multiple
                           accept="image/jpeg,image/png,image/webp">
                    <button type="submit" class="btn btn--outline btn--sm" style="margin-top:.75rem">Upload</button>
                </form>
            </div>
        </section>

        <form method="POST" action="{{ route('listings.update', $pet) }}" enctype="multipart/form-data"
              class="form-grid" style="margin-top:1.5rem">
            @csrf @method('PUT')

            @include('listings.partials.pet-fields')

            <div class="flex flex-wrap items-center justify-between gap-4">
                <button type="submit" form="delete-listing" class="btn btn--ghost btn--sm">Delete listing</button>
                <button type="submit" class="btn btn--accent">Save changes</button>
            </div>
        </form>

        <form method="POST" action="{{ route('listings.destroy', $pet) }}" id="delete-listing"
              onsubmit="return confirm('Remove this listing? Applications are kept.')">
            @csrf @method('DELETE')
        </form>

        @if (in_array($pet->review_status, ['draft', 'rejected'], true))
            <section class="panel" style="margin-top:1.5rem">
                <div class="panel__head"><h2>Ready to go live?</h2></div>
                <div class="panel__body">
                    <p class="field-hint" style="margin-bottom:1rem">
                        Send this listing to our team. We usually review within a day, and you’ll
                        see any notes here.
                    </p>
                    <form method="POST" action="{{ route('listings.submit', $pet) }}">
                        @csrf
                        <button type="submit" class="btn btn--accent">Send for review</button>
                    </form>
                </div>
            </section>
        @endif
    </div>

    @include('listings.partials.form-script')
@endsection
