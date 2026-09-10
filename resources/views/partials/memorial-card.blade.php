{{-- Expects $memorial with candles_count / messages_count loaded. --}}
<a href="{{ route('memorials.show', $memorial) }}" class="memorial-card">
    <div class="memorial-card__photo">
        @if ($memorial->photoUrl())
            <img src="{{ $memorial->photoUrl() }}" alt="{{ $memorial->pet_name }}" loading="lazy">
        @else
            <div class="memorial-card__fallback"><i data-lucide="paw-print" aria-hidden="true"></i></div>
        @endif
    </div>

    <div class="memorial-card__body">
        <h3>{{ $memorial->pet_name }}</h3>
        @if ($memorial->lifespanLabel())
            <p class="memorial-card__years">{{ $memorial->lifespanLabel() }}</p>
        @endif
        @if ($memorial->species)
            <p class="memorial-card__species">{{ $memorial->species }}</p>
        @endif

        <p class="memorial-card__meta">
            <span><i data-lucide="flame" aria-hidden="true"></i> {{ $memorial->candles_count }}</span>
            <span><i data-lucide="message-circle" aria-hidden="true"></i> {{ $memorial->messages_count }}</span>
        </p>
    </div>
</a>
