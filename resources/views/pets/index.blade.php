@extends('layouts.app')

@section('title', 'Adoptable pets — Two Fat Cats')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-semibold text-stone-900">Adoptable pets</h1>
        <p class="mt-1 text-stone-600">{{ $pets->total() }} {{ Str::plural('friend', $pets->total()) }} looking for a home.</p>

        {{-- Filters --}}
        <form method="GET" class="mt-6 grid grid-cols-1 gap-3 rounded-2xl border border-stone-200 bg-white p-4 sm:grid-cols-4">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by name…"
                   class="rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">

            <select name="species" class="rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">All species</option>
                @foreach ($species as $s)
                    <option value="{{ $s->slug }}" @selected(($filters['species'] ?? '') === $s->slug)>{{ $s->name }}</option>
                @endforeach
            </select>

            <select name="size" class="rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Any size</option>
                @foreach (['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'] as $val => $label)
                    <option value="{{ $val }}" @selected(($filters['size'] ?? '') === $val)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button class="flex-1 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">
                    Filter
                </button>
                <a href="{{ route('pets.index') }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm text-stone-600 hover:bg-stone-50">
                    Reset
                </a>
            </div>
        </form>

        {{-- Grid --}}
        @if ($pets->isEmpty())
            <div class="mt-12 rounded-2xl border border-dashed border-stone-300 p-12 text-center text-stone-500">
                No pets match your filters. Try widening your search.
            </div>
        @else
            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($pets as $pet)
                    @include('partials.pet-card')
                @endforeach
            </div>

            <div class="mt-10">
                {{ $pets->links() }}
            </div>
        @endif
    </div>
@endsection
