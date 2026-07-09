@extends('layouts.app')

@section('title', 'Two Fat Cats — Find your new best friend')

@section('content')
    <section class="relative overflow-hidden bg-gradient-to-b from-amber-50 to-stone-50">
        <div class="mx-auto max-w-6xl px-4 py-20 text-center">
            <span class="inline-block rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-800">
                🐾 Adopt, don't shop
            </span>
            <h1 class="mt-6 text-4xl font-semibold tracking-tight text-stone-900 sm:text-5xl">
                Find your new best friend
            </h1>
            <p class="mx-auto mt-4 max-w-xl text-lg text-stone-600">
                Two Fat Cats connects loving homes with pets in need. Browse animals waiting for
                adoption and start your application today.
            </p>
            <div class="mt-8 flex justify-center gap-3">
                <a href="{{ route('pets.index') }}"
                   class="rounded-full bg-amber-600 px-6 py-3 font-medium text-white shadow-sm transition hover:bg-amber-700">
                    Browse adoptable pets
                </a>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-16">
        <div class="mb-8 flex items-end justify-between">
            <h2 class="text-2xl font-semibold text-stone-900">Recently added</h2>
            <a href="{{ route('pets.index') }}" class="text-sm font-medium text-amber-700 hover:underline">
                See all &rarr;
            </a>
        </div>

        @if ($featured->isEmpty())
            <p class="text-stone-500">No pets are listed yet — check back soon!</p>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($featured as $pet)
                    @include('partials.pet-card')
                @endforeach
            </div>
        @endif
    </section>

    <section class="border-t border-stone-200 bg-white">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-16 sm:grid-cols-3">
            <div>
                <div class="text-3xl">🏡</div>
                <h3 class="mt-3 font-semibold text-stone-900">Coming soon: Services</h3>
                <p class="mt-1 text-sm text-stone-600">Grooming, vet visits, boarding and training from trusted local providers.</p>
            </div>
            <div>
                <div class="text-3xl">🛍️</div>
                <h3 class="mt-3 font-semibold text-stone-900">Coming soon: Shop</h3>
                <p class="mt-1 text-sm text-stone-600">Food, toys and accessories — everything your new companion needs.</p>
            </div>
            <div>
                <div class="text-3xl">❤️</div>
                <h3 class="mt-3 font-semibold text-stone-900">Every adoption counts</h3>
                <p class="mt-1 text-sm text-stone-600">Fees go directly toward the care of animals still waiting for a home.</p>
            </div>
        </div>
    </section>
@endsection
