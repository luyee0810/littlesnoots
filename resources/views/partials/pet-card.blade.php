@php($photo = $pet->primaryPhoto())

{{-- An <article>, not an <a>: the CTA below is the real link, so the card
     carries no fake affordance and no nested anchors. --}}
<article class="pet-card">
    <div class="pet-card__photo">
        @if ($photo)
            <img src="{{ $photo->url() }}" alt="{{ $photo->alt ?? 'Photo of '.$pet->name }}" loading="lazy">
        @else
            <div class="pet-card__fallback"><i data-lucide="paw-print" aria-hidden="true"></i></div>
        @endif

        @if ($pet->status !== 'available')
            <span class="pet-card__flag">{{ ucfirst($pet->status) }}</span>
        @endif
    </div>

    <div class="pet-card__body">
        <div class="pet-card__title">
            <h3>{{ $pet->name }}</h3>
            <i data-lucide="{{ strtolower((string) $pet->gender) === 'female' ? 'venus' : 'mars' }}"
               aria-label="{{ ucfirst((string) $pet->gender) }}"></i>
        </div>

        <p class="pet-card__meta">
            <span>{{ $pet->ageForHumans() ?? 'Age on request' }}</span>
            <span class="pet-card__dot" aria-hidden="true"></span>
            <span>{{ $pet->breedLabel() }}</span>
            @if ($pet->location)
                <span class="pet-card__dot" aria-hidden="true"></span>
                <span>{{ $pet->location }}</span>
            @endif
        </p>

        <div class="chip-row">
            @if ($pet->size)<span class="chip chip--sage">{{ ucfirst(str_replace('_', ' ', $pet->size)) }}</span>@endif
            @if ($pet->shots_current)
                <span class="chip chip--sky">Vaccinated</span>
            @else
                <span class="chip chip--butter">Ready to meet</span>
            @endif
        </div>

        @if ($pet->adoption_fee > 0)
            <p class="pet-card__fee">Adoption fee RM {{ number_format($pet->adoption_fee, 0) }}</p>
        @endif

        <a href="{{ route('pets.show', $pet) }}" class="btn btn--accent pet-card__cta">
            Meet {{ $pet->name }} <i data-lucide="paw-print" aria-hidden="true"></i>
        </a>
    </div>
</article>
