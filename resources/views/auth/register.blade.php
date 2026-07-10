@extends('layouts.guest')

@section('title', 'Sign up — Two Fat Cats')

@section('content')
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Get started</p>
        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-stone-900">Create your account</h2>
        <p class="mt-2 text-sm text-stone-500">It takes a minute. Tell us how you'd like to use Two Fat Cats.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        {{-- Account type — the two kinds of member --}}
        <fieldset>
            <legend class="mb-2 text-sm font-medium text-stone-700">I'm here to&hellip;</legend>
            <div class="grid grid-cols-2 gap-3">
                @php($chosen = old('account_type', 'adopter'))
                <label class="cursor-pointer">
                    <input type="radio" name="account_type" value="adopter" class="peer sr-only" @checked($chosen === 'adopter')>
                    <div class="h-full rounded-xl border border-stone-300 bg-white p-4 transition hover:border-amber-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:shadow-sm peer-checked:ring-2 peer-checked:ring-amber-500/25 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-500">
                        <span class="text-2xl">🏡</span>
                        <p class="mt-3 text-sm font-semibold text-stone-900">Adopt a pet</p>
                        <p class="mt-0.5 text-xs leading-snug text-stone-500">Browse pets and apply to bring one home.</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="account_type" value="lister" class="peer sr-only" @checked($chosen === 'lister')>
                    <div class="h-full rounded-xl border border-stone-300 bg-white p-4 transition hover:border-amber-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:shadow-sm peer-checked:ring-2 peer-checked:ring-amber-500/25 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-500">
                        <span class="text-2xl">🐾</span>
                        <p class="mt-3 text-sm font-semibold text-stone-900">Rehome a pet</p>
                        <p class="mt-0.5 text-xs leading-snug text-stone-500">Post pets for adoption and review applicants.</p>
                    </div>
                </label>
            </div>
            @error('account_type') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
        </fieldset>

        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium text-stone-700">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   placeholder="Alex Rivera"
                   class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 @error('name') border-red-400 @enderror">
            @error('name') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-stone-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   placeholder="you@example.com"
                   class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 @error('email') border-red-400 @enderror">
            @error('email') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="mb-1.5 block text-sm font-medium text-stone-700">Phone <span class="font-normal text-stone-400">(optional)</span></label>
            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" autocomplete="tel"
                   placeholder="(555) 123-4567"
                   class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 @error('phone') border-red-400 @enderror">
            @error('phone') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-stone-700">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       placeholder="••••••••"
                       class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 @error('password') border-red-400 @enderror">
                @error('password') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-stone-700">Confirm</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       placeholder="••••••••"
                       class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            </div>
        </div>

        <button class="w-full rounded-xl bg-amber-600 px-6 py-3 font-semibold text-white shadow-sm shadow-amber-600/20 transition hover:bg-amber-700 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 active:translate-y-px">
            Create account
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-stone-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-amber-700 hover:text-amber-800 hover:underline">Log in</a>
    </p>
@endsection
