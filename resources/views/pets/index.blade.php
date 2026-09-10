@extends('layouts.app')

@section('title', 'Adoptable pets — Two Fat Cats')

@php
    $val = fn ($k, $d = '') => $filters[$k] ?? $d;
    $checked = fn ($k) => ! empty($filters[$k]);
@endphp

@section('content')
    <div class="shell page">
        <div class="page-head">
            <div>
                <p class="kicker">Adoption</p>
                <h1>Adoptable pets</h1>
            </div>
            <p class="meta">
                {{ $pets->total() }} {{ Str::plural('friend', $pets->total()) }} looking for a home
            </p>
        </div>

        <div class="results-layout">
            {{-- ---- Filters ------------------------------------------------ --}}
            <form method="GET" class="filters">
                <div class="field">
                    <label for="q">Search</label>
                    <input type="search" id="q" name="q" value="{{ $val('q') }}" placeholder="Name or keyword…" class="input">
                </div>

                @foreach ([
                    'species' => ['Type', $species->pluck('name', 'slug')->all()],
                    'age' => ['Age', ['young' => 'Young', 'adult' => 'Adult', 'senior' => 'Senior']],
                    'gender' => ['Gender', ['male' => 'Male', 'female' => 'Female']],
                    'size' => ['Size', ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large', 'extra_large' => 'Extra large']],
                    'coat' => ['Coat length', ['hairless' => 'Hairless', 'short' => 'Short', 'medium' => 'Medium', 'long' => 'Long', 'wire' => 'Wire', 'curly' => 'Curly']],
                ] as $name => [$label, $options])
                    <div class="field">
                        <label for="filter-{{ $name }}">{{ $label }}</label>
                        <select id="filter-{{ $name }}" name="{{ $name }}" class="select">
                            <option value="">Any</option>
                            @foreach ($options as $key => $option)
                                <option value="{{ $key }}" @selected($val($name) === (string) $key)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach

                <fieldset class="fieldset">
                    <legend>Good in a home with</legend>
                    <div class="check-stack">
                        @foreach (['good_with_children' => 'Children', 'good_with_dogs' => 'Dogs', 'good_with_cats' => 'Cats'] as $k => $label)
                            <label class="check">
                                <input type="checkbox" name="{{ $k }}" value="1" @checked($checked($k))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="fieldset">
                    <legend>Care &amp; behaviour</legend>
                    <div class="check-stack">
                        @foreach (['house_trained' => 'House-trained', 'special_needs' => 'Special needs'] as $k => $label)
                            <label class="check">
                                <input type="checkbox" name="{{ $k }}" value="1" @checked($checked($k))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="filters__actions">
                    <button class="btn btn--primary btn--sm">Apply filters</button>
                    <a href="{{ route('pets.index') }}" class="btn btn--outline btn--sm">Reset</a>
                </div>
            </form>

            {{-- ---- Results ------------------------------------------------ --}}
            <div>
                @if ($pets->isEmpty())
                    <div class="empty">
                        <p class="empty__icon">🔍</p>
                        <h2>No pets match your filters</h2>
                        <p>Try widening your search — fewer filters usually turns up more friends.</p>
                        <a href="{{ route('pets.index') }}" class="btn btn--outline btn--sm">Clear all filters</a>
                    </div>
                @else
                    <div class="card-grid">
                        @foreach ($pets as $pet)
                            @include('partials.pet-card')
                        @endforeach
                    </div>
                    <div class="pagination-wrap">{{ $pets->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
