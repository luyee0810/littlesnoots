@extends('layouts.app')

@section('title', 'My dashboard — Two Fat Cats')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-12">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold text-stone-900">Hello, {{ auth()->user()->name }}</h1>
            <p class="mt-1 text-stone-500">
                {{ auth()->user()->isStaff() ? "Manage the pets you've listed for adoption." : "Track the adoption applications you've submitted." }}
            </p>
        </div>
        <a href="{{ route('pets.index') }}"
           class="rounded-full bg-amber-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-amber-700">
            Browse pets
        </a>
    </div>

    @isset($listedPets)
        {{-- Rehomer view: pets this member has posted for adoption --}}
        <div class="mt-8 space-y-4">
            @forelse ($listedPets as $pet)
                @php($photo = $pet->primaryPhoto())
                <div class="flex items-center gap-4 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-stone-100">
                        @if ($photo)
                            <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center text-3xl">🐾</div>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('pets.show', $pet) }}" class="font-semibold text-stone-900 hover:text-amber-700">{{ $pet->name }}</a>
                        <p class="text-sm text-stone-500">{{ $pet->breedLabel() }}</p>
                        @php($applicationCount = $pet->applications()->count())
                        <p class="mt-1 text-xs text-stone-400">{{ $applicationCount }} {{ \Illuminate\Support\Str::plural('application', $applicationCount) }}</p>
                    </div>
                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium capitalize text-stone-600">{{ $pet->status }}</span>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-10 text-center">
                    <p class="text-lg text-stone-700">You haven't listed any pets yet.</p>
                    <p class="mt-1 text-sm text-stone-500">Listing management is coming soon — you'll be able to post pets right here.</p>
                </div>
            @endforelse
        </div>
    @else
        {{-- Adopter view: applications this member has submitted --}}
        <div class="mt-8 space-y-4">
            @forelse ($applications as $application)
            @php($pet = $application->pet)
            @php($photo = $pet?->primaryPhoto())
            <div class="flex items-center gap-4 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-stone-100">
                    @if ($photo)
                        <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-3xl">🐾</div>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    @if ($pet)
                        <a href="{{ route('pets.show', $pet) }}" class="font-semibold text-stone-900 hover:text-amber-700">{{ $pet->name }}</a>
                        <p class="text-sm text-stone-500">{{ $pet->breedLabel() }}</p>
                    @else
                        <p class="font-semibold text-stone-900">Pet no longer listed</p>
                    @endif
                    <p class="mt-1 text-xs text-stone-400">Applied {{ $application->created_at->format('M j, Y') }}</p>
                </div>
                <span @class([
                    'rounded-full px-3 py-1 text-xs font-medium capitalize',
                    'bg-amber-100 text-amber-800' => $application->status === 'pending',
                    'bg-emerald-100 text-emerald-800' => $application->status === 'approved',
                    'bg-red-100 text-red-700' => $application->status === 'rejected',
                    'bg-stone-100 text-stone-600' => ! in_array($application->status, ['pending', 'approved', 'rejected'], true),
                ])>
                    {{ $application->status }}
                </span>
            </div>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-stone-100 p-10 text-center text-stone-600">
                <p class="text-lg">You haven't applied for any pets yet.</p>
                <a href="{{ route('pets.index') }}" class="mt-3 inline-block font-medium text-amber-700 hover:underline">Find your new best friend &rarr;</a>
            </div>
        @endforelse
        </div>
    @endisset
</div>
@endsection
