@extends('layouts.app')

@section('title', 'Become a sitter — Two Fat Cats')

@php($profile = new \App\Models\ProviderProfile)

@section('content')
    <div class="shell-mid page">
        <div class="page-head">
            <div>
                <p class="kicker">Join the sitter community</p>
                <h1>Become a sitter</h1>
                <p class="lede" style="margin-top:.75rem">
                    Tell pet owners who you are and where you are. You’ll add your services and rates next.
                </p>
            </div>
        </div>

        <div class="steps" style="margin-top:1.75rem">
            <span class="step" data-current><span class="step__n">1</span> About you</span>
            <span class="step"><span class="step__n">2</span> Services &amp; rates</span>
        </div>

        @if ($errors->any())
            <div class="alert alert--bad" style="margin-top:1.5rem">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('provider.onboarding.store') }}" class="form-grid" style="margin-top:2rem">
            @csrf

            @include('provider.partials.profile-fields', ['profile' => $profile, 'species' => $species])

            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="field-hint">You can edit any of this later.</p>
                <button type="submit" class="btn btn--accent">
                    Continue to services <i data-lucide="arrow-right" aria-hidden="true"></i>
                </button>
            </div>
        </form>
    </div>
@endsection
