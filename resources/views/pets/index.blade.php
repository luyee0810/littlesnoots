@extends('layouts.app')

@section('title', 'Adoptable pets — Two Fat Cats')

@php
    $val = fn ($k, $d = '') => $filters[$k] ?? $d;
    $checked = fn ($k) => ! empty($filters[$k]);
@endphp

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-semibold text-stone-900">Adoptable pets</h1>
        <p class="mt-1 text-stone-600">{{ $pets->total() }} {{ Str::plural('friend', $pets->total()) }} looking for a home.</p>

        <div class="mt-6 grid gap-8 lg:grid-cols-[18rem_1fr]">
            {{-- Filter sidebar --}}
            <form method="GET" class="h-max rounded-2xl border border-stone-200 bg-white p-5">
                <div class="space-y-5 text-sm">
                    <div>
                        <label class="mb-1 block font-medium text-stone-700">Search</label>
                        <input type="search" name="q" value="{{ $val('q') }}" placeholder="Name or keyword…"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-500 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="mb-1 block font-medium text-stone-700">Type</label>
                        <select name="species" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-500 focus:ring-amber-500">
                            <option value="">Any</option>
                            @foreach ($species as $s)
                                <option value="{{ $s->slug }}" @selected($val('species') === $s->slug)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block font-medium text-stone-700">Age</label>
                        <select name="age" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-500 focus:ring-amber-500">
                            <option value="">Any</option>
                            @foreach (['baby' => 'Baby', 'young' => 'Young', 'adult' => 'Adult', 'senior' => 'Senior'] as $k => $label)
                                <option value="{{ $k }}" @selected($val('age') === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block font-medium text-stone-700">Gender</label>
                        <select name="gender" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-500 focus:ring-amber-500">
                            <option value="">Any</option>
                            @foreach (['male' => 'Male', 'female' => 'Female'] as $k => $label)
                                <option value="{{ $k }}" @selected($val('gender') === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block font-medium text-stone-700">Size</label>
                        <select name="size" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-500 focus:ring-amber-500">
                            <option value="">Any</option>
                            @foreach (['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large', 'extra_large' => 'Extra Large'] as $k => $label)
                                <option value="{{ $k }}" @selected($val('size') === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block font-medium text-stone-700">Coat length</label>
                        <select name="coat" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-500 focus:ring-amber-500">
                            <option value="">Any</option>
                            @foreach (['hairless', 'short', 'medium', 'long', 'wire', 'curly'] as $k)
                                <option value="{{ $k }}" @selected($val('coat') === $k)>{{ ucfirst($k) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <fieldset>
                        <legend class="mb-2 font-medium text-stone-700">Good in a home with</legend>
                        <div class="space-y-2">
                            @foreach (['good_with_children' => 'Children', 'good_with_dogs' => 'Dogs', 'good_with_cats' => 'Cats'] as $k => $label)
                                <label class="flex items-center gap-2 text-stone-600">
                                    <input type="checkbox" name="{{ $k }}" value="1" @checked($checked($k))
                                           class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="mb-2 font-medium text-stone-700">Care &amp; behaviour</legend>
                        <div class="space-y-2">
                            @foreach (['house_trained' => 'House-trained', 'special_needs' => 'Special needs'] as $k => $label)
                                <label class="flex items-center gap-2 text-stone-600">
                                    <input type="checkbox" name="{{ $k }}" value="1" @checked($checked($k))
                                           class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="flex gap-2 pt-2">
                        <button class="flex-1 rounded-lg bg-amber-600 px-4 py-2 font-medium text-white hover:bg-amber-700">Apply filters</button>
                        <a href="{{ route('pets.index') }}" class="rounded-lg border border-stone-300 px-4 py-2 text-stone-600 hover:bg-stone-50">Reset</a>
                    </div>
                </div>
            </form>

            {{-- Results --}}
            <div>
                @if ($pets->isEmpty())
                    <div class="rounded-2xl border border-dashed border-stone-300 p-12 text-center text-stone-500">
                        No pets match your filters. Try widening your search.
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($pets as $pet)
                            @include('partials.pet-card')
                        @endforeach
                    </div>
                    <div class="mt-10">{{ $pets->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
