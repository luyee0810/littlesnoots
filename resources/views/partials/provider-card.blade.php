@php
    $photo = $provider->primaryPhoto();
    $cheapest = $provider->cheapestService();
@endphp

<a href="{{ route('providers.show', $provider) }}" class="listing" aria-label="View the profile of {{ $provider->user->name }}">
    <div class="listing__media">
        @if ($photo)
            <img src="{{ $photo->url }}" alt="{{ $photo->caption ?? $provider->user->name }}" loading="lazy">
        @else
            <div class="listing__fallback">🐾</div>
        @endif

        @if ($provider->reviews_count > 0)
            <span class="listing__flag">
                <i data-lucide="star" aria-hidden="true" style="width:13px;height:13px;fill:currentColor;color:var(--ochre)"></i>
                {{ number_format($provider->rating_avg, 1) }}
                <span class="meta" style="font-size:.72rem">({{ $provider->reviews_count }})</span>
            </span>
        @endif
    </div>

    <div class="listing__body">
        <div class="listing__title">
            <h3>{{ $provider->user->name }}</h3>
        </div>

        <p class="listing__meta line-clamp-2">{{ $provider->headline }}</p>

        <p class="listing__place"><i data-lucide="map-pin" aria-hidden="true"></i>{{ $provider->locationLabel() }}</p>

        <div class="chip-row">
            @foreach ($provider->services->where('is_active', true)->take(3) as $service)
                <span class="chip">{{ $service->category->icon }} {{ $service->category->name }}</span>
            @endforeach
        </div>

        <div class="listing__foot">
            <span class="listing__cta">View profile <i data-lucide="arrow-right" aria-hidden="true"></i></span>
            @if ($cheapest)
                <span><span class="label" style="display:inline">from</span> <span class="price">{{ $cheapest->priceLabel() }}</span></span>
            @endif
        </div>
    </div>
</a>
