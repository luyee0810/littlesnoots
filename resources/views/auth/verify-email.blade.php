@extends('layouts.guest')

@section('title', 'Confirm your email — Little Snoots')

@section('content')
    <h1 class="auth__title">Confirm your email</h1>
    <p class="auth__sub">
        We’ve sent a link to <strong>{{ auth()->user()->email }}</strong>.
        Click it and you’re done.
    </p>

    @if (session('success'))
        <div class="alert alert--ok" style="margin-top:1.25rem">{{ session('success') }}</div>
    @endif

    <p class="field-hint" style="margin-top:1.25rem">
        You can browse pets and sitters without this. We ask before you list a pet,
        book a sitter or message someone — those all rely on us being able to reach you.
    </p>

    <form method="POST" action="{{ route('verification.send') }}" style="margin-top:1.5rem">
        @csrf
        <button type="submit" class="btn btn--accent btn--block">Send the link again</button>
    </form>

    <div class="auth__alt" style="margin-top:1.25rem">
        <a href="{{ route('pets.index') }}">Keep browsing</a> ·
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="link-quiet" style="border:0;background:none;cursor:pointer;padding:0;font:inherit">Log out</button>
        </form>
    </div>
@endsection
