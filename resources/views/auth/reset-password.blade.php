@extends('layouts.guest')

@section('title', 'Reset password — Little Snoots')

@section('content')
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Password help</p>
        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-stone-900">Choose a new password</h2>
        <p class="mt-2 text-sm text-stone-500">Almost done — set a new password and you're back in.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-stone-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   placeholder="you@example.com"
                   class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 @error('email') border-red-400 @enderror">
            @error('email') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium text-stone-700">New password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   placeholder="••••••••"
                   class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 @error('password') border-red-400 @enderror">
            @error('password') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-stone-700">Confirm new password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   placeholder="••••••••"
                   class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
        </div>

        <button class="w-full rounded-xl bg-amber-600 px-6 py-3 font-semibold text-white shadow-sm shadow-amber-600/20 transition hover:bg-amber-700 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 active:translate-y-px">
            Reset password
        </button>
    </form>
@endsection
