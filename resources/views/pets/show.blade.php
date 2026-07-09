@extends('layouts.app')

@section('title', $pet->name.' — Two Fat Cats')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-10">
        <a href="{{ route('pets.index') }}" class="text-sm text-amber-700 hover:underline">&larr; Back to all pets</a>

        <div class="mt-4 grid gap-10 lg:grid-cols-2">
            {{-- Gallery --}}
            <div>
                @php($photos = $pet->photos)
                <div class="aspect-[4/3] overflow-hidden rounded-2xl bg-stone-100">
                    @if ($photos->isNotEmpty())
                        <img src="{{ $photos->first()->url() }}" alt="{{ $photos->first()->alt }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-6xl">🐾</div>
                    @endif
                </div>
                @if ($photos->count() > 1)
                    <div class="mt-3 grid grid-cols-4 gap-3">
                        @foreach ($photos as $photo)
                            <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" class="aspect-square w-full rounded-lg object-cover">
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Summary --}}
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-semibold text-stone-900">{{ $pet->name }}</h1>
                    <span @class([
                        'rounded-full px-3 py-1 text-xs font-medium capitalize',
                        'bg-emerald-100 text-emerald-800' => $pet->status === 'available',
                        'bg-amber-100 text-amber-800' => $pet->status === 'pending',
                        'bg-stone-200 text-stone-600' => in_array($pet->status, ['adopted', 'found', 'unavailable']),
                    ])>{{ $pet->status }}</span>
                </div>
                <p class="mt-1 text-stone-600">{{ $pet->breedLabel() }} · {{ $pet->species->name }}</p>

                {{-- Characteristics --}}
                <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-stone-500">Age</dt><dd class="font-medium capitalize">{{ $pet->age_group ?? '—' }}{{ $pet->ageForHumans() && $pet->age_months ? ' ('.$pet->ageForHumans().')' : '' }}</dd></div>
                    <div><dt class="text-stone-500">Gender</dt><dd class="font-medium capitalize">{{ $pet->gender }}</dd></div>
                    <div><dt class="text-stone-500">Size</dt><dd class="font-medium capitalize">{{ $pet->size ? str_replace('_', ' ', $pet->size) : '—' }}</dd></div>
                    <div><dt class="text-stone-500">Coat length</dt><dd class="font-medium capitalize">{{ $pet->coat ?? '—' }}</dd></div>
                    <div><dt class="text-stone-500">Color</dt><dd class="font-medium">{{ $pet->colorLabel() ?? '—' }}</dd></div>
                    <div><dt class="text-stone-500">Adoption fee</dt><dd class="font-medium text-amber-700">{{ $pet->adoption_fee > 0 ? '$'.number_format($pet->adoption_fee, 0) : 'Free' }}</dd></div>
                </dl>

                {{-- Personality tags --}}
                @if (! empty($pet->tags))
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-stone-500">Personality</h3>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($pet->tags as $tag)
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-800">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($pet->status === 'available')
                    <a href="#apply" class="mt-8 inline-block rounded-full bg-amber-600 px-6 py-3 font-medium text-white shadow-sm transition hover:bg-amber-700">
                        Considering {{ $pet->name }} for adoption?
                    </a>
                @endif
            </div>
        </div>

        {{-- About + attributes --}}
        <div class="mt-12 grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <h2 class="text-xl font-semibold text-stone-900">Meet {{ $pet->name }}</h2>
                @if ($pet->description)
                    <p class="mt-3 whitespace-pre-line leading-relaxed text-stone-700">{{ $pet->description }}</p>
                @else
                    <p class="mt-3 text-stone-500">No description provided yet.</p>
                @endif

                <div class="mt-8 grid gap-8 sm:grid-cols-2">
                    <div>
                        <h3 class="font-medium text-stone-900">Health &amp; care</h3>
                        <ul class="mt-3 space-y-2 text-sm">
                            @foreach ([
                                'spayed_neutered' => 'Spayed / neutered',
                                'shots_current'   => 'Vaccinations up to date',
                                'house_trained'   => 'House-trained',
                                'declawed'        => 'Declawed',
                                'special_needs'   => 'Special needs',
                            ] as $flag => $label)
                                <li class="flex items-center gap-2 {{ $pet->$flag ? 'text-stone-700' : 'text-stone-400' }}">
                                    <span>{{ $pet->$flag ? '✓' : '—' }}</span> {{ $label }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-medium text-stone-900">Good in a home with</h3>
                        <ul class="mt-3 space-y-2 text-sm">
                            @foreach ([
                                'good_with_children' => 'Children',
                                'good_with_dogs'     => 'Dogs',
                                'good_with_cats'     => 'Cats',
                            ] as $flag => $label)
                                <li class="flex items-center gap-2 {{ $pet->$flag ? 'text-stone-700' : 'text-stone-400' }}">
                                    <span>{{ $pet->$flag ? '✓' : '—' }}</span> {{ $label }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Organization / shelter card --}}
            @if ($pet->organization)
                @php($org = $pet->organization)
                <aside class="h-max rounded-2xl border border-stone-200 bg-white p-6">
                    <p class="text-xs font-medium uppercase tracking-wide text-stone-400">{{ ucfirst($org->type) }}</p>
                    <h3 class="mt-1 font-semibold text-stone-900">{{ $org->name }}</h3>
                    @if ($org->fullAddress())
                        <p class="mt-2 text-sm text-stone-600">📍 {{ $org->fullAddress() }}</p>
                    @endif
                    <dl class="mt-4 space-y-1 text-sm text-stone-600">
                        @if ($org->phone)<div>📞 {{ $org->phone }}</div>@endif
                        @if ($org->email)<div>✉️ <a href="mailto:{{ $org->email }}" class="text-amber-700 hover:underline">{{ $org->email }}</a></div>@endif
                        @if ($org->website)<div>🌐 <a href="{{ $org->website }}" class="text-amber-700 hover:underline">Website</a></div>@endif
                    </dl>
                    @if ($org->hours)
                        <details class="mt-4 text-sm">
                            <summary class="cursor-pointer font-medium text-stone-700">Opening hours</summary>
                            <ul class="mt-2 space-y-0.5 text-stone-600">
                                @foreach ($org->hours as $day => $time)
                                    <li class="flex justify-between"><span class="capitalize">{{ $day }}</span><span>{{ $time }}</span></li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                    @if ($org->adoption_policy)
                        <details class="mt-3 text-sm">
                            <summary class="cursor-pointer font-medium text-stone-700">Adoption policy</summary>
                            <p class="mt-2 text-stone-600">{{ $org->adoption_policy }}</p>
                        </details>
                    @endif
                </aside>
            @endif
        </div>

        {{-- Adoption application --}}
        @if ($pet->status === 'available')
            <div id="apply" class="mt-12 rounded-2xl border border-stone-200 bg-white p-6 sm:p-8">
                <h2 class="text-xl font-semibold text-stone-900">Apply to adopt {{ $pet->name }}</h2>
                <p class="mt-1 text-sm text-stone-600">Fill in your details and {{ $pet->organization?->name ?? 'our team' }} will reach out to arrange a meet.</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">Please correct the errors below.</div>
                @endif

                <form method="POST" action="{{ route('pets.apply', $pet) }}" class="mt-6 grid gap-4 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-stone-700">Your name *</label>
                        <input name="applicant_name" value="{{ old('applicant_name') }}" required
                               class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                        @error('applicant_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700">Email *</label>
                        <input type="email" name="applicant_email" value="{{ old('applicant_email') }}" required
                               class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                        @error('applicant_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700">Phone</label>
                        <input name="applicant_phone" value="{{ old('applicant_phone') }}"
                               class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700">Home type</label>
                        <select name="home_type" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                            <option value="">Prefer not to say</option>
                            <option value="apartment" @selected(old('home_type') === 'apartment')>Apartment</option>
                            <option value="house" @selected(old('home_type') === 'house')>House</option>
                            <option value="other" @selected(old('home_type') === 'other')>Other</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-stone-700">Why would you be a great match?</label>
                        <textarea name="message" rows="4"
                                  class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">{{ old('message') }}</textarea>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-stone-700 sm:col-span-2">
                        <input type="checkbox" name="has_other_pets" value="1" @checked(old('has_other_pets'))
                               class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                        I currently have other pets at home
                    </label>
                    <div class="sm:col-span-2">
                        <button class="rounded-full bg-amber-600 px-6 py-3 font-medium text-white shadow-sm transition hover:bg-amber-700">Submit application</button>
                    </div>
                </form>
            </div>
        @else
            <div class="mt-12 rounded-2xl border border-stone-200 bg-stone-100 p-6 text-center text-stone-600">
                {{ $pet->name }} is no longer available for adoption.
            </div>
        @endif
    </div>
@endsection
