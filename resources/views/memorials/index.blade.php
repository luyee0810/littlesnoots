@extends('layouts.app')

@section('title', 'Pet Memorials — In loving memory')

@section('content')
    {{-- ---- Header --------------------------------------------------------- --}}
    <section class="band memorial-intro memorial-intro--video">
        {{-- Meadow clip behind the header; a paper scrim over it keeps the
             forest ink legible, and the edges feather into the band's wash. --}}
        <video class="memorial-intro__bg" autoplay loop muted playsinline preload="metadata"
               poster="{{ asset('images/grassdog-poster.jpg') }}" aria-hidden="true">
            <source src="{{ asset('videos/grassdog.mp4') }}" type="video/mp4">
        </video>
        <div class="shell">
            <div class="band-head band-head--center">
                <div>
                    <p class="kicker kicker--center">In loving memory</p>
                    <h1>A garden of remembrance</h1>
                    <p class="lede memorial-intro__lede">
                        For the companions who filled our homes with joy. Create a lasting
                        tribute, light a candle, and share the memories that live on.
                    </p>
                    <div class="memorial-intro__actions">
                        @auth
                            <a href="{{ route('memorials.create') }}" class="btn btn--accent">
                                Create a memorial <i data-lucide="feather" aria-hidden="true"></i>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn--accent">
                                Log in to create a memorial <i data-lucide="arrow-right" aria-hidden="true"></i>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ---- Grid ----------------------------------------------------------- --}}
    <section class="band band--raised">
        <div class="shell">
            @if ($memorials->isEmpty())
                <div class="empty">
                    <p class="empty__icon">🕯️</p>
                    <h2>No memorials yet</h2>
                    <p>Be the first to honour a beloved pet.</p>
                    @auth
                        <a href="{{ route('memorials.create') }}" class="btn btn--outline btn--sm">Create a memorial</a>
                    @endauth
                </div>
            @else
                <div class="card-grid card-grid--4 memorial-grid">
                    @foreach ($memorials as $memorial)
                        @include('partials.memorial-card')
                    @endforeach
                </div>

                <div class="pagination-wrap">
                    {{ $memorials->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
