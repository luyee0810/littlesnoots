{{-- Phase 2 search: keywords + location + service. Shared by /services and results.
     Expects $categories, and optionally $filters. --}}
@php
    $filters = $filters ?? [];
    $val = fn ($k) => $filters[$k] ?? '';
    $selectedCats = (array) ($filters['category'] ?? []);
    $selectedNames = $categories->whereIn('slug', $selectedCats)->pluck('name');
    $catLabel = $selectedNames->isEmpty()
        ? 'Any service'
        : ($selectedNames->count() === 1 ? $selectedNames->first() : $selectedNames->count().' services');
@endphp

<form method="GET" action="{{ route('services.index') }}" class="searchbar" role="search">
    <div>
        <label for="q" class="sr-only">Keywords</label>
        <input type="search" id="q" name="q" value="{{ $val('q') }}" class="input"
               placeholder="Boarding, grooming, puppy training…">
    </div>

    <div>
        <label for="location" class="sr-only">Location</label>
        <input type="search" id="location" name="location" value="{{ $val('location') }}" class="input"
               placeholder="Town or postcode">
    </div>

    <div class="multiselect">
        <details class="multiselect__details">
            <summary class="select multiselect__summary" role="button" aria-haspopup="listbox">
                <span>{{ $catLabel }}</span>
            </summary>
            <div class="multiselect__panel" role="listbox" aria-label="Service">
                @foreach ($categories as $c)
                    <label class="multiselect__option">
                        <input type="checkbox" name="category[]" value="{{ $c->slug }}"
                               @checked(in_array($c->slug, $selectedCats, true))>
                        <span>{{ $c->name }}</span>
                    </label>
                @endforeach
            </div>
        </details>
    </div>

    <button type="submit" class="btn btn--primary">
        <i data-lucide="search" aria-hidden="true"></i> Search
    </button>
</form>
