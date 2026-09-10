@extends('layouts.app')

@section('title', 'In memory of '.$memorial->pet_name.' — Two Fat Cats')

@section('content')
    <article class="memorial">
        {{-- ---- Tribute header --------------------------------------------- --}}
        <header class="memorial-hero">
            <div class="shell-narrow">
                <a href="{{ route('memorials.index') }}" class="back-link">
                    <i data-lucide="arrow-left" aria-hidden="true"></i> All memorials
                </a>

                <div class="memorial-hero__inner">
                    <div class="memorial-hero__portrait">
                        @if ($memorial->photoUrl())
                            <img src="{{ $memorial->photoUrl() }}" alt="{{ $memorial->pet_name }}">
                        @else
                            <div class="memorial-hero__fallback"><i data-lucide="paw-print" aria-hidden="true"></i></div>
                        @endif
                    </div>

                    <div class="memorial-hero__id">
                        <p class="kicker">In loving memory</p>
                        <h1>{{ $memorial->pet_name }}</h1>
                        @if ($memorial->lifespanLabel())
                            <p class="memorial-hero__years">{{ $memorial->lifespanLabel() }}</p>
                        @endif
                        @if ($memorial->species)
                            <p class="memorial-hero__species">{{ $memorial->species }}</p>
                        @endif
                        <p class="memorial-hero__author">Remembered by {{ $memorial->user->name }}</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="shell-narrow memorial-body">
            {{-- ---- Candles -------------------------------------------------- --}}
            <div class="candle-bar">
                <div class="candle-bar__count">
                    <span class="candle-bar__flame @if ($memorial->candles->isNotEmpty()) is-lit @endif" aria-hidden="true">
                        <i data-lucide="flame"></i>
                    </span>
                    <div>
                        <b>{{ $memorial->candles->count() }}</b>
                        <small>{{ Str::plural('candle', $memorial->candles->count()) }} lit</small>
                    </div>
                </div>

                @auth
                    <form method="POST" action="{{ route('memorials.candle', $memorial) }}">
                        @csrf
                        <button type="submit" class="btn {{ $lit ? 'btn--outline' : 'btn--accent' }}">
                            <i data-lucide="flame" aria-hidden="true"></i>
                            {{ $lit ? 'Put out my candle' : 'Light a candle' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn--accent">
                        <i data-lucide="flame" aria-hidden="true"></i> Light a candle
                    </a>
                @endauth
            </div>

            {{-- ---- The tribute ---------------------------------------------- --}}
            <div class="tribute-text">
                {!! nl2br(e($memorial->tribute)) !!}
            </div>

            @if (auth()->id() === $memorial->user_id)
                <form method="POST" action="{{ route('memorials.destroy', $memorial) }}" class="memorial-owner"
                      onsubmit="return confirm('Remove this memorial? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn--ghost btn--sm">Remove memorial</button>
                </form>
            @endif

            {{-- ---- Guestbook ------------------------------------------------ --}}
            <section class="guestbook" id="guestbook">
                <div class="band-head">
                    <div>
                        <p class="kicker">Guestbook</p>
                        <h2>Messages &amp; condolences</h2>
                    </div>
                </div>

                @auth
                    <form method="POST" action="{{ route('memorials.messages.store', $memorial) }}" class="guestbook__form">
                        @csrf
                        <div class="field">
                            <label for="body" class="sr-only">Leave a message</label>
                            <textarea name="body" id="body" rows="3" required maxlength="1000" class="textarea"
                                      placeholder="Share a memory or leave your condolences…">{{ old('body') }}</textarea>
                            @error('body')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="btn btn--accent btn--sm">Post message</button>
                        </div>
                    </form>
                @else
                    <p class="guestbook__signin">
                        <a href="{{ route('login') }}">Log in</a> to leave a message.
                    </p>
                @endauth

                @forelse ($memorial->messages as $message)
                    <div class="guestbook__entry">
                        <div class="guestbook__avatar" aria-hidden="true">{{ Str::upper(Str::substr($message->user->name, 0, 1)) }}</div>
                        <div>
                            <p class="guestbook__byline">
                                <b>{{ $message->user->name }}</b>
                                <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->diffForHumans() }}</time>
                            </p>
                            <p class="guestbook__body">{{ $message->body }}</p>
                        </div>
                    </div>
                @empty
                    <p class="guestbook__empty">No messages yet — be the first to leave one.</p>
                @endforelse
            </section>
        </div>
    </article>
@endsection
