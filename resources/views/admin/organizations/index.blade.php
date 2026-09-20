@extends('layouts.app')

@section('title', 'Shelters — Admin')

@section('content')
    <div class="shell-wide page">
        @include('admin.partials.nav')

        <div class="page-head" style="margin-top:1.5rem">
            <div>
                <p class="kicker">Back of house</p>
                <h1>Shelters &amp; rescues</h1>
                <p class="lede" style="margin-top:.5rem">
                    Records a listing can point at. Individuals rehome without one.
                </p>
            </div>
            <a href="{{ route('admin.organizations.create') }}" class="btn btn--accent btn--sm">Add a shelter</a>
        </div>

        <div class="filter-bar" style="margin-top:1.25rem">
            <form method="GET" action="{{ route('admin.organizations.index') }}" class="filter-bar__search">
                <input type="search" name="q" class="input input--sm" value="{{ request('q') }}"
                       placeholder="Search shelters" aria-label="Search shelters">
            </form>
        </div>

        <div class="stack" style="margin-top:1.5rem">
            @forelse ($organizations as $organization)
                <article class="panel listing-row">
                    <div class="listing-row__media">
                        <div class="listing-row__placeholder" aria-hidden="true"><i data-lucide="house"></i></div>
                    </div>

                    <div class="listing-row__body">
                        <h2>{{ $organization->name }}</h2>
                        <p class="field-hint">
                            {{ ucfirst($organization->type) }}
                            @if ($organization->fullAddress()) · {{ $organization->fullAddress() }} @endif
                        </p>
                        <p style="margin-top:.5rem">
                            <span class="badge">{{ $organization->pets_count }} {{ Str::plural('listing', $organization->pets_count) }}</span>
                            @if ($organization->email)<span class="badge">{{ $organization->email }}</span>@endif
                        </p>
                    </div>

                    <div class="listing-row__actions">
                        <a href="{{ route('admin.organizations.edit', $organization) }}" class="btn btn--outline btn--sm">Edit</a>
                    </div>
                </article>
            @empty
                <div class="empty">
                    <p><strong>No shelters yet.</strong></p>
                    <p class="field-hint">Add one when a rescue starts listing with you.</p>
                </div>
            @endforelse
        </div>

        <div style="margin-top:1.5rem">{{ $organizations->links() }}</div>
    </div>
@endsection
