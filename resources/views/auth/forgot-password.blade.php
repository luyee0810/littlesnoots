@extends('layouts.guest')

@section('title', 'Forgot password — Little Snoots')

@section('content')
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Password help</p>
        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-stone-900">Forgot your password?</h2>
        <p class="mt-2 text-sm text-stone-500">Enter your email and we'll send you a link to set a new one.</p>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-stone-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   placeholder="you@example.com"
                   class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 @error('email') border-red-400 @enderror">
            @error('email') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
        </div>

        <button class="w-full rounded-xl bg-amber-600 px-6 py-3 font-semibold text-white shadow-sm shadow-amber-600/20 transition hover:bg-amber-700 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 active:translate-y-px">
            Email me a reset link
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-stone-500">
        <a href="{{ route('login') }}" class="font-semibold text-amber-700 hover:text-amber-800 hover:underline">&larr; Back to log in</a>
    </p>
@endsection
