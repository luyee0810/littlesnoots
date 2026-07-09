@php($photo = $pet->primaryPhoto())
<a href="{{ route('pets.show', $pet) }}"
   class="group flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
    <div class="relative aspect-[4/3] overflow-hidden bg-stone-100">
        @if ($photo)
            <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}"
                 loading="lazy"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center text-4xl">🐾</div>
        @endif
        @if ($pet->age_group)
            <span class="absolute left-2 top-2 rounded-full bg-white/90 px-2 py-0.5 text-xs font-medium capitalize text-stone-700 shadow-sm">
                {{ $pet->age_group }}
            </span>
        @endif
    </div>
    <div class="flex flex-1 flex-col gap-1 p-4">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-stone-900">{{ $pet->name }}</h3>
            <span class="text-xs capitalize text-stone-500">{{ $pet->gender }}</span>
        </div>
        <p class="text-sm text-stone-500">{{ $pet->breedLabel() }}</p>
        @if ($pet->location)
            <p class="text-xs text-stone-400">📍 {{ $pet->location }}</p>
        @endif
        <div class="mt-2 flex flex-wrap gap-1.5">
            @if ($pet->size)
                <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs capitalize text-stone-600">{{ $pet->size }}</span>
            @endif
            @if ($pet->special_needs)
                <span class="rounded-full bg-purple-50 px-2 py-0.5 text-xs text-purple-700">Special needs</span>
            @endif
            @if ($pet->shots_current)
                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">Vaccinated</span>
            @endif
        </div>
        <div class="mt-3 text-sm font-medium text-amber-700">
            {{ $pet->adoption_fee > 0 ? '$'.number_format($pet->adoption_fee, 0).' fee' : 'Free to a good home' }}
        </div>
    </div>
</a>
