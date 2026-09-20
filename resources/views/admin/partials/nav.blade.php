{{-- Back-of-house nav. Deliberately distinct from the public header so it's
     always obvious which side of the site you're on. --}}
@php($awaiting = \App\Models\Pet::awaitingReview()->count())
@php($awaitingSitters = \App\Models\ProviderProfile::awaitingApproval()->count())
@php($openApplications = \App\Models\AdoptionApplication::open()->count())
<nav class="admin-nav" aria-label="Admin">
    <span class="admin-nav__brand"><i data-lucide="shield" aria-hidden="true"></i> Admin</span>
    <a href="{{ route('admin.dashboard') }}" @class(['admin-nav__link', 'is-on' => request()->routeIs('admin.dashboard')])>Overview</a>
    <a href="{{ route('admin.pets.index') }}" @class(['admin-nav__link', 'is-on' => request()->routeIs('admin.pets.*')])>
        Listings
        @if ($awaiting)
            <span class="admin-nav__count">{{ $awaiting }}</span>
        @endif
    </a>
    <a href="{{ route('admin.applications.index') }}" @class(['admin-nav__link', 'is-on' => request()->routeIs('admin.applications.*')])>
        Applications
        @if ($openApplications)
            <span class="admin-nav__count">{{ $openApplications }}</span>
        @endif
    </a>
    <a href="{{ route('admin.providers.index') }}" @class(['admin-nav__link', 'is-on' => request()->routeIs('admin.providers.*')])>
        Sitters
        @if ($awaitingSitters)
            <span class="admin-nav__count">{{ $awaitingSitters }}</span>
        @endif
    </a>
    <a href="{{ route('home') }}" class="admin-nav__link admin-nav__link--exit">Back to site</a>
</nav>
