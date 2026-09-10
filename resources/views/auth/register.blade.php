@extends('layouts.app')

@section('title', 'Create your account — Two Fat Cats')

@section('content')
    <div class="shell-mid page">
        <div class="page-head">
            <div>
                <p class="kicker">Get started</p>
                <h1>Create your account</h1>
                <p class="lede" style="margin-top:.75rem">
                    It takes a minute. Tell us how you&rsquo;d like to use Two Fat Cats and we&rsquo;ll
                    set the rest up around you.
                </p>
            </div>
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

        <form method="POST" action="{{ route('register') }}" class="form-grid" style="margin-top:2rem">
            @csrf

            {{-- Account type — how the member plans to use the site --}}
            <fieldset class="fieldset">
                <legend>I&rsquo;m here to&hellip;</legend>

                @php($chosen = old('account_type', 'adopter'))
                <div class="choice-grid">
                    @foreach ([
                        ['value' => 'adopter', 'icon' => 'house', 'title' => 'Adopt or care for a pet', 'blurb' => 'Browse pets, apply to adopt, and book sitters, groomers or walkers.'],
                        ['value' => 'shelter', 'icon' => 'paw-print', 'title' => 'List pets for adoption', 'blurb' => 'A shelter, a rescue, or rehoming your own pet — post listings and review applicants.'],
                        ['value' => 'provider', 'icon' => 'heart', 'title' => 'Offer pet services', 'blurb' => 'Boarding, walking, grooming and more. We’ll set up your sitter profile next.'],
                    ] as $option)
                        <label class="choice">
                            <input type="radio" name="account_type" value="{{ $option['value'] }}" @checked($chosen === $option['value'])>
                            <span class="choice__box">
                                <span class="choice__icon"><i data-lucide="{{ $option['icon'] }}" aria-hidden="true"></i></span>
                                <strong>{{ $option['title'] }}</strong>
                                <small>{{ $option['blurb'] }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>

                @error('account_type') <p class="field-error" style="margin-top:.5rem">{{ $message }}</p> @enderror

                <p class="field-hint" style="margin-top:.7rem">
                    This only sets where you start — it doesn&rsquo;t lock you in. Sign up to offer services and you can
                    still post a pet for adoption later; sign up to list pets and you can add a services profile any time.
                    One account does all of it.
                </p>
            </fieldset>

            <div class="field">
                <label for="name">Name</label>
                <input id="name" class="input" type="text" name="name" value="{{ old('name') }}" required autofocus
                       autocomplete="name" placeholder="Alex Rivera" @error('name') aria-invalid="true" @enderror>
                @error('name') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required
                       autocomplete="username" placeholder="you@example.com" @error('email') aria-invalid="true" @enderror>
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="phone">Phone <span class="field-label__optional">(optional)</span></label>
                <input id="phone" class="input" type="text" name="phone" value="{{ old('phone') }}" autocomplete="tel"
                       placeholder="+60 12-345 6789" @error('phone') aria-invalid="true" @enderror>
                @error('phone') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-grid form-grid--2">
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" class="input" type="password" name="password" required
                           autocomplete="new-password" placeholder="••••••••" @error('password') aria-invalid="true" @enderror>
                    @error('password') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" class="input" type="password" name="password_confirmation" required
                           autocomplete="new-password" placeholder="••••••••">
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="field-hint">
                    Already have an account?
                    <a href="{{ route('login') }}" class="link-quiet">Log in</a>
                </p>
                <button type="submit" class="btn btn--accent">
                    Create account <i data-lucide="arrow-right" aria-hidden="true"></i>
                </button>
            </div>
        </form>
    </div>
@endsection
