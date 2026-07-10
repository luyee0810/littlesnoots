@extends('layouts.guest')

@section('title', 'Log in — Two Fat Cats')

@section('content')
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Welcome back</p>
        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-stone-900">Log in to your account</h2>
        <p class="mt-2 text-sm text-stone-500">Pick up where you left off and check on your pets.</p>
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-stone-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   placeholder="you@example.com"
                   class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 @error('email') border-red-400 @enderror">
            @error('email') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-stone-700">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs font-medium text-amber-700 hover:text-amber-800 hover:underline">Forgot password?</a>
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 @error('password') border-red-400 @enderror">
            @error('password') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2.5 text-sm text-stone-600">
            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-stone-300 text-amber-600 focus:ring-amber-500/40">
            Keep me logged in
        </label>

        <button class="w-full rounded-xl bg-amber-600 px-6 py-3 font-semibold text-white shadow-sm shadow-amber-600/20 transition hover:bg-amber-700 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 active:translate-y-px">
            Log in
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-stone-500">
        New to Two Fat Cats?
        <a href="{{ route('register') }}" class="font-semibold text-amber-700 hover:text-amber-800 hover:underline">Create an account</a>
    </p>
@endsection
