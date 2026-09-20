@extends('layouts.app')

@section('title', 'Members — Admin')

@section('content')
    <div class="shell-wide page">
        @include('admin.partials.nav')

        <div class="page-head" style="margin-top:1.5rem">
            <div>
                <p class="kicker">Back of house</p>
                <h1>Members</h1>
            </div>
        </div>

        <div class="filter-bar" style="margin-top:1.25rem">
            @foreach (['all' => 'Everyone', 'sitters' => 'Sitters', 'staff' => 'Staff', 'suspended' => 'Suspended', 'unverified' => 'Unverified email'] as $key => $label)
                <a href="{{ route('admin.users.index', ['filter' => $key, 'q' => request('q')]) }}"
                   @class(['chip', 'is-on' => $filter === $key])>
                    {{ $label }}
                    @if ($counts[$key] ?? 0)
                        <span class="chip__count">{{ $counts[$key] }}</span>
                    @endif
                </a>
            @endforeach

            <form method="GET" action="{{ route('admin.users.index') }}" class="filter-bar__search">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="search" name="q" class="input input--sm" value="{{ request('q') }}"
                       placeholder="Name or email" aria-label="Search members">
            </form>
        </div>

        <div class="stack" style="margin-top:1.5rem">
            @forelse ($users as $member)
                <article class="panel listing-row">
                    <div class="listing-row__media">
                        <div class="listing-row__placeholder avatar-initial" aria-hidden="true">
                            {{ Str::upper(Str::substr($member->name, 0, 1)) }}
                        </div>
                    </div>

                    <div class="listing-row__body">
                        <h2>{{ $member->name }}</h2>
                        <p class="field-hint">
                            {{ $member->email }}
                            @unless ($member->email_verified_at)
                                · <span title="Notifications may never reach them">unverified</span>
                            @endunless
                            · joined {{ $member->created_at->format('M Y') }}
                        </p>
                        <p style="margin-top:.5rem">
                            @if ($member->isSuspended())
                                <span class="badge badge--rejected">Suspended</span>
                            @endif
                            @if ($member->role !== 'adopter')
                                <span class="badge badge--approved">{{ ucfirst($member->role) }}</span>
                            @endif
                            @if ($member->providerProfile)
                                <span class="badge badge--submitted">Sitter</span>
                            @endif
                            @if ($member->listed_pets_count)
                                <span class="badge">{{ $member->listed_pets_count }} listed</span>
                            @endif
                            @if ($member->bookings_count)
                                <span class="badge">{{ $member->bookings_count }} {{ Str::plural('booking', $member->bookings_count) }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="listing-row__actions">
                        <a href="{{ route('admin.users.show', $member) }}" class="btn btn--outline btn--sm">Open</a>
                    </div>
                </article>
            @empty
                <div class="empty">
                    <p><strong>No members found.</strong></p>
                    <p class="field-hint">Nobody matches this filter.</p>
                </div>
            @endforelse
        </div>

        <div style="margin-top:1.5rem">{{ $users->links() }}</div>
    </div>
@endsection
