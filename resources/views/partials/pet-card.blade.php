@php($photo = $pet->primaryPhoto())

<a href="{{ route('pets.show', $pet) }}" class="listing" aria-label="Meet {{ $pet->name }}">
    <div class="listing__media">
        @if ($photo)
            <img src="{{ $photo->url() }}" alt="{{ $photo->alt ?? 'Photo of '.$pet->name }}" loading="lazy">
        @else
            <div class="listing__fallback"><i data-lucide="paw-print" aria-hidden="true"></i></div>
        @endif

        @if ($pet->status !== 'available')
            <span class="listing__flag">{{ ucfirst($pet->status) }}</span>
        @endif
    </div>

    <div class="listing__body">
        <div class="listing__title">
            <h3>{{ $pet->name }}</h3>
            <i data-lucide="{{ strtolower((string) $pet->gender) === 'female' ? 'venus' : 'mars' }}"
               aria-label="{{ ucfirst((string) $pet->gender) }}"></i>
        </div>

        <p class="listing__meta">
            {{ $pet->ageForHumans() ?? 'Age on request' }} · {{ $pet->breedLabel() }}
        </p>

        @if ($pet->location)
            <p class="listing__place"><i data-lucide="map-pin" aria-hidden="true"></i>{{ $pet->location }}</p>
        @endif

        <div class="chip-row">
            @if ($pet->size)<span class="chip chip--sage">{{ ucfirst(str_replace('_', ' ', $pet->size)) }}</span>@endif
            @if ($pet->shots_current)
                <span class="chip chip--sky">Vaccinated</span>
            @else
                <span class="chip chip--butter">Ready to meet</span>
            @endif
        </div>

        <div class="listing__foot">
            <span class="listing__cta">Meet {{ $pet->name }} <i data-lucide="arrow-right" aria-hidden="true"></i></span>
            @if ($pet->adoption_fee > 0)
                <span class="meta">RM {{ number_format($pet->adoption_fee, 0) }}</span>
            @endif
        </div>
    </div>
</a>
