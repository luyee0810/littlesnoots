@extends('layouts.app')

@section('title', "Review {$provider->user?->name} — Admin")

@section('content')
    <div class="shell-mid page">
        @include('admin.partials.nav')

        <div class="page-head" style="margin-top:1.5rem">
            <div>
                <p class="kicker"><a href="{{ route('admin.providers.index') }}">← All sitters</a></p>
                <h1>{{ $provider->user?->name }}</h1>
                <p class="lede" style="margin-top:.35rem">{{ $provider->headline }}</p>
                <p style="margin-top:.5rem">
                    <span class="badge badge--{{ $provider->status === 'pending' ? 'submitted' : ($provider->status === 'approved' ? 'approved' : 'rejected') }}">
                        {{ $provider->statusLabel() }}
                    </span>
                    @if ($provider->reviewed_at)
                        <span class="field-hint">· {{ $provider->reviewer?->name }} {{ $provider->reviewed_at->diffForHumans() }}</span>
                    @endif
                    @if ($provider->isLive())
                        · <a href="{{ route('providers.show', $provider) }}">View public profile</a>
                    @endif
                </p>
            </div>
        </div>

        @include('listings.partials.errors')

        @if ($provider->photos->isNotEmpty())
            <div class="photo-grid" style="margin-top:1.5rem">
                @foreach ($provider->photos as $photo)
                    <figure class="photo-grid__item">
                        <img src="{{ $photo->url }}" alt="" loading="lazy">
                    </figure>
                @endforeach
            </div>
        @endif

        <section class="panel" style="margin-top:1.5rem">
            <div class="panel__head"><h2>Their profile</h2></div>
            <div class="panel__body">
                <dl class="detail-grid">
                    <div><dt>Email</dt><dd>{{ $provider->user?->email }}</dd></div>
                    <div><dt>Location</dt><dd>{{ $provider->locationLabel() }}</dd></div>
                    <div><dt>Experience</dt><dd>{{ $provider->years_experience }} years</dd></div>
                    <div><dt>Home</dt><dd>{{ $provider->home_type ? ucfirst(str_replace('_', ' ', $provider->home_type)) : '—' }}</dd></div>
                    <div><dt>Takes</dt><dd>{{ collect($provider->accepts_species)->map(fn ($s) => ucfirst($s))->join(', ') ?: '—' }}</dd></div>
                    <div><dt>Sizes</dt><dd>{{ collect($provider->accepts_sizes)->map(fn ($s) => ucfirst($s))->join(', ') ?: '—' }}</dd></div>
                    <div><dt>Joined</dt><dd>{{ $provider->created_at->format('j M Y') }}</dd></div>
                    <div><dt>Bookings</dt><dd>{{ $provider->bookings()->count() }}</dd></div>
                </dl>

                <div style="margin-top:1.25rem">
                    <h3>About</h3>
                    <p style="margin-top:.5rem;white-space:pre-line">{{ $provider->bio ?: 'No bio written.' }}</p>
                </div>
            </div>
        </section>

        <section class="panel" style="margin-top:1.5rem">
            <div class="panel__head">
                <h2>Services &amp; rates</h2>
                <p class="panel__note">A sitter with no services has nothing to book.</p>
            </div>
            <div class="panel__body">
                @forelse ($provider->services as $service)
                    <div class="row">
                        <div>
                            <strong>{{ $service->category?->name }}</strong>
                            <p class="field-hint">{{ $service->title }}</p>
                        </div>
                        <span>RM {{ number_format((float) $service->price, 2) }} / {{ $service->category?->pricing_unit }}</span>
                    </div>
                @empty
                    <p class="field-hint">No services listed yet.</p>
                @endforelse
            </div>
        </section>

        <section class="panel" style="margin-top:1.5rem">
            <div class="panel__head">
                <h2>Decision</h2>
                <p class="panel__note">Approving lists this sitter for owners to book.</p>
            </div>
            <div class="panel__body form-grid">
                @if ($provider->status !== 'approved')
                    <form method="POST" action="{{ route('admin.providers.approve', $provider) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn--accent">Approve &amp; list</button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.providers.suspend', $provider) }}" class="form-grid">
                    @csrf @method('PATCH')
                    <div class="field">
                        <label for="review_notes">
                            {{ $provider->isLive() ? 'Why are you taking this profile down?' : 'What needs changing?' }}
                        </label>
                        <textarea name="review_notes" id="review_notes" rows="3" class="textarea" required
                                  minlength="10" maxlength="1000"
                                  placeholder="The sitter sees this — e.g. please add a photo of the room pets would stay in.">{{ old('review_notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn--outline">
                        {{ $provider->isLive() ? 'Take down' : 'Send back to sitter' }}
                    </button>
                </form>
            </div>
        </section>
    </div>
@endsection
