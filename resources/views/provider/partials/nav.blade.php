@php
    $tabs = [
        'bookings' => ['label' => 'Bookings', 'route' => 'provider.bookings.index'],
        'services' => ['label' => 'Services & rates', 'route' => 'provider.services.index'],
        'profile' => ['label' => 'Profile', 'route' => 'provider.profile.edit'],
    ];
@endphp

<div>
    <p class="kicker">Sitter dashboard</p>
    <nav class="tabs" aria-label="Sitter dashboard">
        @foreach ($tabs as $key => $tab)
            <a href="{{ route($tab['route']) }}" class="tab"
               @if (($active ?? '') === $key) aria-current="page" @endif>{{ $tab['label'] }}</a>
        @endforeach

        @if (auth()->user()?->providerProfile)
            <a href="{{ route('providers.show', auth()->user()->providerProfile) }}" class="tab" style="margin-left:auto">
                View public profile
                <i data-lucide="arrow-right" aria-hidden="true" style="display:inline-block;width:13px;height:13px;vertical-align:-1px"></i>
            </a>
        @endif
    </nav>
</div>
