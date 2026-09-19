@extends('layouts.app')

@section('title', 'Log in — Little Snoots')

@section('content')
    <div class="shell-mid page">
        <div class="page-head">
            <div>
                <p class="kicker">Welcome back</p>
                <h1>Log in to your account</h1>
                <p class="lede" style="margin-top:.75rem">
                    Pick up where you left off and check on your pets.
                </p>
            </div>
        </div>

        {{-- session('success') is already flashed by layouts.app --}}
        @if (session('status'))
            <div class="alert alert--warn" style="margin-top:1.5rem">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="form-grid form-grid--narrow" style="margin-top:2rem">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required autofocus
                       autocomplete="username" placeholder="you@example.com" @error('email') aria-invalid="true" @enderror>
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <div class="flex items-center justify-between">
                    <label for="password" class="field-label">Password</label>
                    <a href="{{ route('password.request') }}" class="link-quiet text-xs">Forgot password?</a>
                </div>
                <input id="password" class="input" type="password" name="password" required
                       autocomplete="current-password" placeholder="••••••••" @error('password') aria-invalid="true" @enderror>
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <label class="check">
                <input type="checkbox" name="remember">
                Keep me logged in
            </label>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="field-hint">
                    New to Little Snoots?
                    <a href="{{ route('register') }}" class="link-quiet">Create an account</a>
                </p>
                <button type="submit" class="btn btn--accent">
                    Log in <i data-lucide="arrow-right" aria-hidden="true"></i>
                </button>
            </div>
        </form>
    </div>
@endsection
