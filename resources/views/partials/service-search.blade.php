{{-- Phase 2 search: keywords + location + service. Shared by /services and results.
     Expects $categories, and optionally $filters. --}}
@php
    $filters = $filters ?? [];
    $val = fn ($k) => $filters[$k] ?? '';
@endphp

<form method="GET" action="{{ route('services.index') }}" class="searchbar" role="search">
    <div>
        <label for="q" class="sr-only">Keywords</label>
        <input type="search" id="q" name="q" value="{{ $val('q') }}" class="input"
               placeholder="Cat sitter, grooming, puppy training…">
    </div>

    <div>
        <label for="location" class="sr-only">Location</label>
        <input type="search" id="location" name="location" value="{{ $val('location') }}" class="input"
               placeholder="Town or postcode">
    </div>

    <div>
        <label for="category" class="sr-only">Service</label>
        <select id="category" name="category" class="select">
            <option value="">Any service</option>
            @foreach ($categories as $c)
                <option value="{{ $c->slug }}" @selected($val('category') === $c->slug)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="btn btn--primary">
        <i data-lucide="search" aria-hidden="true"></i> Search
    </button>
</form>
