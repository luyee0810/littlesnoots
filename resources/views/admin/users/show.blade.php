@extends('layouts.app')

@section('title', "{$user->name} — Admin")

@php($actor = auth()->user())
@php($canModerate = $user->canBeModeratedBy($actor))

@section('content')
    <div class="shell-mid page">
        @include('admin.partials.nav')

        <div class="page-head" style="margin-top:1.5rem">
            <div>
                <p class="kicker"><a href="{{ route('admin.users.index') }}">← All members</a></p>
                <h1>{{ $user->name }}</h1>
                <p class="lede" style="margin-top:.35rem">
                    <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                    @if ($user->phone) · {{ $user->phone }} @endif
                </p>
                <p style="margin-top:.5rem">
                    @if ($user->isSuspended())
                        <span class="badge badge--rejected">Suspended</span>
                    @endif
                    <span class="badge badge--approved">{{ ucfirst($user->role) }}</span>
                    @if ($user->providerProfile)
                        <span class="badge badge--submitted">{{ $user->providerProfile->statusLabel() }} sitter</span>
                    @endif
                    @if (! $user->email_verified_at)
                        <span class="badge">Email unverified</span>
                    @endif
                </p>
            </div>
        </div>

        @include('listings.partials.errors')

        @if ($user->isSuspended())
            <div class="alert alert--bad" style="margin-top:1.5rem">
                <strong>Suspended {{ $user->suspended_at->diffForHumans() }}</strong>
                @if ($user->suspender) by {{ $user->suspender->name }} @endif
                <p style="margin-top:.35rem">{{ $user->suspension_reason }}</p>
            </div>
        @endif

        {{-- ---- What they've done ------------------------------------------ --}}
        <div class="stat-row" style="margin-top:1.5rem">
            <div class="stat">
                <span class="stat__n">{{ $user->listedPets->count() }}</span>
                <span class="stat__label">Pets listed</span>
            </div>
            <div class="stat">
                <span class="stat__n">{{ $user->adoptionApplications->count() }}</span>
                <span class="stat__label">Adoption applications</span>
            </div>
            <div class="stat">
                <span class="stat__n">{{ $user->bookings->count() }}</span>
                <span class="stat__label">Bookings made</span>
            </div>
            <div class="stat">
                <span class="stat__n">{{ $user->memorials->count() }}</span>
                <span class="stat__label">Memorials</span>
            </div>
        </div>

        @if ($user->providerProfile)
            <section class="panel" style="margin-top:1.5rem">
                <div class="panel__head">
                    <h2>Sitter profile</h2>
                    <a href="{{ route('admin.providers.show', $user->providerProfile) }}" class="panel__note">Open sitter review</a>
                </div>
                <div class="panel__body">
                    <p>{{ $user->providerProfile->headline }}</p>
                    <p class="field-hint" style="margin-top:.35rem">
                        {{ $user->providerProfile->locationLabel() }} ·
                        {{ $user->providerProfile->statusLabel() }}
                    </p>
                </div>
            </section>
        @endif

        @if ($user->listedPets->isNotEmpty())
            <section class="panel" style="margin-top:1.5rem">
                <div class="panel__head"><h2>Pets they’ve listed</h2></div>
                <div class="panel__body rows">
                    @foreach ($user->listedPets as $pet)
                        <div class="row">
                            <div>
                                <a href="{{ route('admin.pets.show', $pet) }}" style="font-weight:600">{{ $pet->name }}</a>
                                <p class="field-hint">{{ $pet->reviewLabel() }} · {{ ucfirst($pet->status) }}</p>
                            </div>
                            <span class="field-hint">{{ $pet->created_at->format('j M Y') }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($user->bookings->isNotEmpty())
            <section class="panel" style="margin-top:1.5rem">
                <div class="panel__head"><h2>Bookings</h2></div>
                <div class="panel__body rows">
                    @foreach ($user->bookings as $booking)
                        <div class="row">
                            <div>
                                <strong>{{ $booking->reference }}</strong>
                                <p class="field-hint">
                                    {{ $booking->category?->name }} with {{ $booking->providerProfile?->user?->name ?? 'unknown' }}
                                </p>
                            </div>
                            <span class="badge">{{ str_replace('_', ' ', $booking->status) }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ---- Account actions -------------------------------------------- --}}
        <section class="panel" style="margin-top:1.5rem">
            <div class="panel__head">
                <h2>Account</h2>
                <p class="panel__note">
                    @if (! $canModerate)
                        {{ $actor->id === $user->id ? 'You can’t moderate your own account.' : 'Only an admin can act on another staff member.' }}
                    @endif
                </p>
            </div>

            @if ($canModerate)
                <div class="panel__body form-grid">
                    @if ($actor->isAdmin())
                        <form method="POST" action="{{ route('admin.users.role', $user) }}" class="form-grid">
                            @csrf @method('PATCH')
                            <div class="field" style="max-width:16rem">
                                <label for="role">Role</label>
                                <select name="role" id="role" class="input">
                                    @foreach (['adopter' => 'Adopter', 'staff' => 'Staff', 'admin' => 'Admin'] as $value => $label)
                                        <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <p class="field-hint">Staff reach the admin area; admins can also change roles.</p>
                            </div>
                            <button type="submit" class="btn btn--outline btn--sm">Update role</button>
                        </form>
                    @endif

                    @if ($user->isSuspended())
                        <form method="POST" action="{{ route('admin.users.reinstate', $user) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn--accent btn--sm">Lift suspension</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.users.suspend', $user) }}" class="form-grid">
                            @csrf @method('PATCH')
                            <div class="field">
                                <label for="suspension_reason">Suspend this account</label>
                                <textarea name="suspension_reason" id="suspension_reason" rows="2" class="textarea"
                                          required minlength="10" maxlength="255"
                                          placeholder="They see this at the login screen — e.g. repeated abusive messages on memorials.">{{ old('suspension_reason') }}</textarea>
                                <p class="field-hint">
                                    They’re signed out immediately. Their listings, bookings and history are kept.
                                </p>
                            </div>
                            <button type="submit" class="btn btn--outline btn--sm">Suspend</button>
                        </form>
                    @endif
                </div>
            @endif
        </section>
    </div>
@endsection
