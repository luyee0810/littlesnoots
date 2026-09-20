@extends('layouts.app')

@section('title', 'Booking '.$booking->reference.' — Little Snoots')

@php
    $provider = $booking->providerProfile;
    $isOwner = $booking->user_id === auth()->id();
@endphp

@section('content')
    <div class="shell-mid page">
        <a href="{{ $isOwner ? route('dashboard') : route('provider.bookings.index') }}" class="back-link">
            <i data-lucide="arrow-left" aria-hidden="true"></i> Back to {{ $isOwner ? 'dashboard' : 'bookings' }}
        </a>

        <div class="page-head" style="margin-top:1.5rem">
            <div>
                <p class="kicker">Booking {{ $booking->reference }}</p>
                <h1>{{ $booking->category->name }} for {{ $booking->pet_name }}</h1>
                <p class="meta" style="margin-top:.5rem">Requested {{ $booking->created_at->diffForHumans() }}</p>
            </div>
            <span class="status {{ $booking->statusClasses() }}">{{ $booking->statusLabel() }}</span>
        </div>

        @if ($booking->isAwaitingResponse() && $booking->expires_at)
            <div class="alert alert--warn" style="margin-top:1.5rem">
                Waiting on {{ $provider->user->name }} — this request lapses {{ $booking->expires_at->diffForHumans() }}.
            </div>
        @endif

        @if ($booking->provider_response)
            <div class="card card-pad" style="margin-top:1.5rem">
                <p class="label">{{ $provider->user->name }} replied</p>
                <p class="prose" style="margin-top:.5rem">{{ $booking->provider_response }}</p>
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2" style="margin-top:2rem">
            <section class="panel">
                <div class="panel__head"><h2>The service</h2></div>
                <div class="panel__body" style="padding-block:.5rem">
                    <dl class="dl">
                        <div>
                            <dt>Service</dt>
                            <dd><i class="svc-icon" data-lucide="{{ $booking->category->lucideIcon() }}" aria-hidden="true"></i> {{ $booking->category->name }}</dd>
                        </div>
                        <div><dt>When</dt><dd>{{ $booking->dateRangeLabel() }}</dd></div>
                        <div>
                            <dt>Duration</dt>
                            <dd>{{ $booking->unit_quantity }} {{ Str::plural($booking->unit_label, $booking->unit_quantity) }}</dd>
                        </div>
                        <div>
                            <dt>Sitter</dt>
                            <dd><a href="{{ route('providers.show', $provider) }}" class="link-quiet">{{ $provider->user->name }}</a></dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="panel">
                <div class="panel__head"><h2>The pet</h2></div>
                <div class="panel__body" style="padding-block:.5rem">
                    <dl class="dl">
                        <div><dt>Name</dt><dd>{{ $booking->pet_name }}</dd></div>
                        @if ($booking->pet_breed)<div><dt>Breed</dt><dd>{{ $booking->pet_breed }}</dd></div>@endif
                        @if ($booking->pet_size)<div><dt>Size</dt><dd class="capitalize">{{ $booking->pet_size }}</dd></div>@endif
                        <div><dt>Number of pets</dt><dd>{{ $booking->pet_count }}</dd></div>
                    </dl>
                    @if ($booking->pet_notes)
                        <p class="meta" style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--rule-faint)">{{ $booking->pet_notes }}</p>
                    @endif
                </div>
            </section>
        </div>

        <section class="panel" style="margin-top:1.25rem">
            <div class="panel__head"><h2>Agreed price</h2></div>
            <div class="panel__body" style="padding-block:.5rem">
                <dl class="dl">
                    <div>
                        <dt>RM {{ number_format($booking->unit_price, 2) }} × {{ $booking->unit_quantity }} {{ Str::plural($booking->unit_label, $booking->unit_quantity) }}</dt>
                        <dd>RM {{ number_format($booking->unit_price * $booking->unit_quantity, 2) }}</dd>
                    </div>
                    @if ($booking->additional_pet_price && $booking->pet_count > 1)
                        <div>
                            <dt>{{ $booking->pet_count - 1 }} extra {{ Str::plural('pet', $booking->pet_count - 1) }}</dt>
                            <dd>RM {{ number_format($booking->total - $booking->unit_price * $booking->unit_quantity, 2) }}</dd>
                        </div>
                    @endif
                    <div style="border-top:1px solid var(--rule);padding-top:1rem">
                        <dt style="font-size:.8rem;color:var(--ink)">Total</dt>
                        <dd class="price" style="font-size:1.35rem">{{ $booking->totalLabel() }}</dd>
                    </div>
                </dl>
                <p class="field-hint" style="margin-top:1rem">
                    Payment is arranged directly between you and the sitter — Little Snoots doesn’t collect it.
                </p>
            </div>
        </section>

        @if ($booking->message)
            <section class="panel" style="margin-top:1.25rem">
                <div class="panel__head"><h2>Your message</h2></div>
                <div class="panel__body"><p class="prose">{{ $booking->message }}</p></div>
            </section>
        @endif

        {{-- ---- Messages --------------------------------------------------- --}}
        <section class="panel" id="messages" style="margin-top:1.25rem;scroll-margin-top:6rem">
            <div class="panel__head">
                <h2>Messages</h2>
                <p class="panel__note">
                    Between you and {{ $booking->counterpartFor(auth()->user())?->name ?? 'the other party' }}
                </p>
            </div>
            <div class="panel__body">
                @forelse ($booking->messages as $message)
                    @php($mine = $message->user_id === auth()->id())
                    <div @class(['chat-msg', 'chat-msg--mine' => $mine])>
                        <p class="chat-msg__byline">
                            <b>{{ $mine ? 'You' : $message->user?->name ?? 'Deleted account' }}</b>
                            <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->diffForHumans() }}</time>
                        </p>
                        <p class="chat-msg__body">{{ $message->body }}</p>

                        @unless ($mine)
                            @include('partials.content-actions', [
                                'content' => $message,
                                'type' => 'booking-message',
                            ])
                        @endunless
                    </div>
                @empty
                    <p class="field-hint">
                        No messages yet. Anything you agree here stays on the record —
                        useful if a booking goes wrong.
                    </p>
                @endforelse

                @if ($booking->isParticipant(auth()->user()))
                    <form method="POST" action="{{ route('bookings.messages.store', $booking) }}"
                          class="chat-form">
                        @csrf
                        <label for="body" class="sr-only">Your message</label>
                        <textarea name="body" id="body" rows="3" required maxlength="2000" class="textarea"
                                  placeholder="Ask a question, share pick-up details…">{{ old('body') }}</textarea>
                        @error('body')<p class="field-error">{{ $message }}</p>@enderror
                        <div class="flex justify-end">
                            <button type="submit" class="btn btn--accent btn--sm">Send</button>
                        </div>
                    </form>
                @else
                    <p class="field-hint" style="margin-top:1rem">
                        You’re viewing this as an admin — you can read the thread but not post in it.
                    </p>
                @endif
            </div>
        </section>

        @if ($booking->review)
            <section class="panel" style="margin-top:1.25rem">
                <div class="panel__head"><h2>{{ $isOwner ? 'Your review' : 'Review from '.$booking->owner_name }}</h2></div>
                <div class="panel__body">
                    <span class="stars" role="img" aria-label="Rated {{ $booking->review->rating }} out of 5">{{ str_repeat('★', $booking->review->rating) }}<span class="stars__off">{{ str_repeat('★', 5 - $booking->review->rating) }}</span></span>
                    @if ($booking->review->body)
                        <p class="prose" style="margin-top:.5rem">{{ $booking->review->body }}</p>
                    @endif
                </div>
            </section>
        @elseif (auth()->user()->can('review', $booking))
            <section class="panel" style="margin-top:1.25rem">
                <div class="panel__head"><h2>How did it go?</h2></div>
                <div class="panel__body">
                    <form method="POST" action="{{ route('bookings.review.store', $booking) }}">
                        @csrf
                        <fieldset>
                            <legend class="field-label">Your rating</legend>
                            <div class="star-picker">
                                @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="rating" id="rating-{{ $i }}" value="{{ $i }}" required
                                           @checked((int) old('rating') === $i)>
                                    <label for="rating-{{ $i }}" title="{{ $i }} {{ Str::plural('star', $i) }}">
                                        <span aria-hidden="true">★</span><span class="sr-only">{{ $i }} {{ Str::plural('star', $i) }}</span>
                                    </label>
                                @endfor
                            </div>
                            @error('rating')<p class="field-error">{{ $message }}</p>@enderror
                        </fieldset>

                        <div class="field" style="margin-top:1rem">
                            <label for="body">Your review <span class="field-label__optional">optional</span></label>
                            <textarea name="body" id="body" rows="4" maxlength="2000" class="textarea"
                                      placeholder="What went well? Anything other owners should know?">{{ old('body') }}</textarea>
                            @error('body')<p class="field-error">{{ $message }}</p>@enderror
                            <p class="field-hint">Shown on {{ $provider->user->name }}’s profile with your first name and last initial.</p>
                        </div>

                        <div class="flex justify-end" style="margin-top:1rem">
                            <button type="submit" class="btn btn--accent btn--sm">Post review</button>
                        </div>
                    </form>
                </div>
            </section>
        @endif

        @can('cancel', $booking)
            <div style="margin-top:2rem">
                <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                      onsubmit="return confirm('Cancel this booking?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn--outline btn--danger btn--sm">Cancel booking</button>
                </form>
            </div>
        @endcan
    </div>
@endsection
