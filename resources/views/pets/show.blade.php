@extends('layouts.app')

@section('title', $pet->name.' — Two Fat Cats')

@php
    $photos = $pet->photos;
    $statusTone = match ($pet->status) {
        'available' => 'status--ok',
        'pending' => 'status--warn',
        default => 'status--idle',
    };
@endphp

@section('content')
    <div class="shell page">
        <a href="{{ route('pets.index') }}" class="back-link">
            <i data-lucide="arrow-left" aria-hidden="true"></i> All pets
        </a>

        <div class="mt-6 grid gap-10 lg:grid-cols-2">
            {{-- ---- Gallery ------------------------------------------------ --}}
            <div>
                <div class="listing__media" style="border-radius:var(--r-md);border:1px solid var(--rule)">
                    @if ($photos->isNotEmpty())
                        <img src="{{ $photos->first()->url() }}" alt="{{ $photos->first()->alt ?? 'Photo of '.$pet->name }}">
                    @else
                        <div class="listing__fallback"><i data-lucide="paw-print" aria-hidden="true"></i></div>
                    @endif
                </div>

                @if ($photos->count() > 1)
                    <div class="mt-3 grid grid-cols-4 gap-3">
                        @foreach ($photos as $photo)
                            <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" loading="lazy"
                                 class="aspect-square w-full object-cover"
                                 style="border-radius:var(--r-sm);border:1px solid var(--rule)">
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ---- Summary ------------------------------------------------ --}}
            <div>
                <p class="kicker">{{ $pet->species->name }}</p>

                <div class="flex flex-wrap items-center gap-4">
                    <h1 style="font-size:clamp(2.4rem,5vw,3.4rem)">{{ $pet->name }}</h1>
                    <span class="status {{ $statusTone }}">{{ $pet->status }}</span>
                </div>

                <p class="lede" style="margin-top:.5rem">{{ $pet->breedLabel() }}</p>

                @if (! empty($pet->tags))
                    <div class="chip-row" style="margin-top:1.25rem">
                        @foreach ($pet->tags as $tag)
                            <span class="chip chip--butter">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                <dl class="dl dl--cols" style="margin-top:1.75rem">
                    <div>
                        <dt>Age</dt>
                        <dd>{{ $pet->ageForHumans() ?? (ucfirst((string) $pet->age_group) ?: '—') }}</dd>
                    </div>
                    <div><dt>Gender</dt><dd class="capitalize">{{ $pet->gender ?: '—' }}</dd></div>
                    <div><dt>Size</dt><dd class="capitalize">{{ $pet->size ? str_replace('_', ' ', $pet->size) : '—' }}</dd></div>
                    <div><dt>Coat</dt><dd class="capitalize">{{ $pet->coat ?: '—' }}</dd></div>
                    <div><dt>Colour</dt><dd>{{ $pet->colorLabel() ?: '—' }}</dd></div>
                    <div>
                        <dt>Adoption fee</dt>
                        <dd class="price">{{ $pet->adoption_fee > 0 ? 'RM '.number_format($pet->adoption_fee, 0) : 'Free' }}</dd>
                    </div>
                </dl>

                @if ($pet->status === 'available')
                    <a href="#apply" class="btn btn--accent" style="margin-top:2rem">
                        I want to know more about {{ $pet->name }} <i data-lucide="arrow-right" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </div>

        {{-- ---- About + shelter -------------------------------------------- --}}
        <div class="section-gap grid gap-10 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
            <div>
                <h2 style="font-size:1.75rem">Meet {{ $pet->name }}</h2>
                @if ($pet->description)
                    <p class="prose" style="margin-top:1rem">{{ $pet->description }}</p>
                @else
                    <p class="meta" style="margin-top:1rem">No description provided yet.</p>
                @endif

                <div class="section-gap grid gap-8 sm:grid-cols-2">
                    <div>
                        <p class="label">Health &amp; care</p>
                        <ul class="ticks" style="margin-top:.9rem">
                            @foreach ([
                                'spayed_neutered' => 'Spayed / neutered',
                                'shots_current' => 'Vaccinations up to date',
                                'house_trained' => 'House-trained',
                                'declawed' => 'Declawed',
                                'special_needs' => 'Special needs',
                            ] as $flag => $label)
                                <li @if (! $pet->$flag) data-off @endif>
                                    <span class="tick" aria-hidden="true">{{ $pet->$flag ? '✓' : '–' }}</span>
                                    {{ $label }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <p class="label">Good in a home with</p>
                        <ul class="ticks" style="margin-top:.9rem">
                            @foreach ([
                                'good_with_children' => 'Children',
                                'good_with_dogs' => 'Dogs',
                                'good_with_cats' => 'Cats',
                            ] as $flag => $label)
                                <li @if (! $pet->$flag) data-off @endif>
                                    <span class="tick" aria-hidden="true">{{ $pet->$flag ? '✓' : '–' }}</span>
                                    {{ $label }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            @if ($pet->organization)
                @php($org = $pet->organization)
                <aside class="card card-pad" style="align-self:start">
                    <p class="label">{{ ucfirst($org->type) }}</p>
                    <h3 style="margin-top:.5rem;font-size:1.3rem">{{ $org->name }}</h3>

                    @if ($org->fullAddress())
                        <p class="listing__place"><i data-lucide="map-pin" aria-hidden="true"></i>{{ $org->fullAddress() }}</p>
                    @endif

                    <dl class="dl" style="margin-top:1.25rem">
                        @if ($org->phone)<div><dt>Phone</dt><dd>{{ $org->phone }}</dd></div>@endif
                        @if ($org->email)
                            <div><dt>Email</dt><dd><a href="mailto:{{ $org->email }}" class="link-quiet">{{ $org->email }}</a></dd></div>
                        @endif
                        @if ($org->website)
                            <div><dt>Website</dt><dd><a href="{{ $org->website }}" class="link-quiet">Visit site</a></dd></div>
                        @endif
                    </dl>

                    @if ($org->hours)
                        <details style="margin-top:1.25rem">
                            <summary class="label" style="cursor:pointer">Opening hours</summary>
                            <dl class="dl" style="margin-top:.5rem">
                                @foreach ($org->hours as $day => $time)
                                    <div><dt>{{ $day }}</dt><dd>{{ $time }}</dd></div>
                                @endforeach
                            </dl>
                        </details>
                    @endif

                    @if ($org->adoption_policy)
                        <details style="margin-top:1rem">
                            <summary class="label" style="cursor:pointer">Adoption policy</summary>
                            <p class="meta" style="margin-top:.5rem">{{ $org->adoption_policy }}</p>
                        </details>
                    @endif
                </aside>
            @endif
        </div>

        {{-- ---- Enquiry ----------------------------------------------------- --}}
        @if ($pet->status === 'available')
            <section id="apply" class="panel section-gap">
                <div class="panel__head">
                    <div>
                        <h2>I want to know more about {{ $pet->name }}</h2>
                        <p class="meta" style="margin-top:.25rem">
                            Ask anything — you don't have to be ready to adopt.
                            {{ $pet->organization?->name ?? 'Our team' }} will get back to you.
                        </p>
                    </div>
                </div>

                <div class="panel__body">
                    @if ($errors->any())
                        <div class="alert alert--bad" style="margin-bottom:1.5rem">Please correct the errors below.</div>
                    @endif

                    <form method="POST" action="{{ route('pets.apply', $pet) }}" class="form-grid form-grid--2">
                        @csrf

                        <div class="field">
                            <label for="applicant_name">Your name *</label>
                            <input id="applicant_name" name="applicant_name" value="{{ old('applicant_name') }}" required
                                   class="input" @error('applicant_name') aria-invalid="true" @enderror>
                            @error('applicant_name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="field">
                            <label for="applicant_email">Email *</label>
                            <input id="applicant_email" type="email" name="applicant_email" value="{{ old('applicant_email') }}" required
                                   class="input" @error('applicant_email') aria-invalid="true" @enderror>
                            @error('applicant_email')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="field">
                            <label for="applicant_phone">Phone</label>
                            <input id="applicant_phone" name="applicant_phone" value="{{ old('applicant_phone') }}" class="input">
                        </div>

                        <div class="field">
                            <label for="home_type">Home type <span class="meta">(optional)</span></label>
                            <select id="home_type" name="home_type" class="select">
                                <option value="">Prefer not to say</option>
                                @foreach (['apartment' => 'Apartment', 'house' => 'House', 'other' => 'Other'] as $k => $label)
                                    <option value="{{ $k }}" @selected(old('home_type') === $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field span-2">
                            <label for="message">What would you like to know?</label>
                            <textarea id="message" name="message" rows="4" class="textarea"
                                      placeholder="e.g. How is {{ $pet->name }} with other pets? Can I visit before deciding?">{{ old('message') }}</textarea>
                        </div>

                        <label class="check span-2">
                            <input type="checkbox" name="has_other_pets" value="1" @checked(old('has_other_pets'))>
                            I currently have other pets at home
                        </label>

                        <div class="span-2">
                            <button class="btn btn--accent">Send my enquiry <i data-lucide="arrow-right" aria-hidden="true"></i></button>
                        </div>
                    </form>
                </div>
            </section>
        @else
            <div class="empty section-gap">
                <p class="empty__icon">🏡</p>
                <h2>{{ $pet->name }} is no longer available</h2>
                <p>They've found their home — but plenty of others are still looking.</p>
                <a href="{{ route('pets.index') }}" class="btn btn--outline btn--sm">Browse available pets</a>
            </div>
        @endif
    </div>
@endsection
