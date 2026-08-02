@php
    // The form drives itself off the category's pricing unit: date-range services
    // (boarding, daycare) show an end date, single-shot ones don't.
    $serviceMeta = $services->mapWithKeys(fn ($s) => [$s->id => [
        'unit' => $s->category->pricing_unit,
        'range' => (bool) $s->category->requires_date_range,
        'price' => (float) $s->price,
        'extra' => (float) ($s->additional_pet_price ?? 0),
        'maxPets' => $s->max_pets,
        'name' => $s->label(),
    ]]);
    $selected = old('provider_service_id', $services->first()?->id);
@endphp

<div class="panel">
    @if ($services->isEmpty())
        <div class="panel__body"><p class="meta">This sitter isn’t taking bookings right now.</p></div>
    @elseif (! $provider->isLive())
        <div class="panel__body"><p class="meta">Publish your profile to start receiving bookings.</p></div>
    @else
        <div class="panel__head">
            <h2>Request a booking</h2>
            <span class="chip chip--sage">No payment now</span>
        </div>

        <div class="panel__body">
            @auth
                @if ($errors->any())
                    <div class="alert alert--bad" style="margin-bottom:1.25rem">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('bookings.store', $provider) }}" class="form-grid" id="booking-form">
                    @csrf

                    <div class="field">
                        <label for="provider_service_id">Service</label>
                        <select name="provider_service_id" id="provider_service_id" required class="select">
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected((int) $selected === $service->id)>
                                    {{ $service->label() }} — RM {{ number_format($service->price, 0) }} {{ $service->category->priceSuffix() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-grid form-grid--2">
                        <div class="field">
                            <label for="starts_at">Start</label>
                            <input type="datetime-local" name="starts_at" id="starts_at" required class="input"
                                   value="{{ old('starts_at') }}" min="{{ now()->addDay()->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="field" data-end-date>
                            <label for="ends_at">End</label>
                            <input type="datetime-local" name="ends_at" id="ends_at" class="input" value="{{ old('ends_at') }}">
                        </div>
                    </div>

                    <div class="form-grid form-grid--2">
                        <div class="field">
                            <label for="pet_name">Pet’s name</label>
                            <input type="text" name="pet_name" id="pet_name" required class="input" value="{{ old('pet_name') }}">
                        </div>
                        <div class="field">
                            <label for="pet_count">How many</label>
                            <input type="number" name="pet_count" id="pet_count" required min="1" class="input"
                                   value="{{ old('pet_count', 1) }}">
                        </div>
                    </div>

                    <div class="form-grid form-grid--2">
                        <div class="field">
                            <label for="pet_species_id">Type</label>
                            <select name="pet_species_id" id="pet_species_id" class="select">
                                <option value="">Select…</option>
                                @foreach ($species as $s)
                                    <option value="{{ $s->id }}" @selected(old('pet_species_id') == $s->id)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="pet_size">Size</label>
                            <select name="pet_size" id="pet_size" class="select">
                                <option value="">Select…</option>
                                @foreach (['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'] as $k => $label)
                                    <option value="{{ $k }}" @selected(old('pet_size') === $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" rows="3" class="textarea"
                                  placeholder="Tell {{ $provider->user->name }} about your pet…">{{ old('message') }}</textarea>
                    </div>

                    {{-- Contact details, pre-filled from the account. --}}
                    <input type="hidden" name="owner_name" value="{{ old('owner_name', auth()->user()->name) }}">
                    <input type="hidden" name="owner_email" value="{{ old('owner_email', auth()->user()->email) }}">

                    <div class="field">
                        <label for="owner_phone">Your phone</label>
                        <input type="tel" name="owner_phone" id="owner_phone" class="input"
                               value="{{ old('owner_phone', auth()->user()->phone) }}" placeholder="012-345 6789">
                    </div>

                    <div class="estimate" data-estimate>
                        <span class="meta">Choose your dates to see an estimate.</span>
                    </div>

                    <button type="submit" class="btn btn--accent btn--block">Send booking request</button>

                    <p class="meta" style="text-align:center;font-size:.78rem">
                        {{ $provider->user->name }} has {{ \App\Models\Booking::RESPONSE_WINDOW_HOURS }} hours to respond.
                        You’ll settle payment directly with them.
                    </p>
                </form>

                @if ($blockedDates->isNotEmpty())
                    <p class="meta" style="margin-top:1rem;font-size:.78rem">
                        Unavailable: {{ $blockedDates->take(4)->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->format('j M'))->implode(', ') }}@if ($blockedDates->count() > 4) and {{ $blockedDates->count() - 4 }} more @endif
                    </p>
                @endif

                @push('scripts')
                    <script>
                        (function () {
                            const meta = @json($serviceMeta);
                            const form = document.getElementById('booking-form');
                            if (!form) return;

                            const select = form.querySelector('#provider_service_id');
                            const endWrap = form.querySelector('[data-end-date]');
                            const endInput = form.querySelector('#ends_at');
                            const starts = form.querySelector('#starts_at');
                            const pets = form.querySelector('#pet_count');
                            const estimate = form.querySelector('[data-estimate]');

                            const nights = () => {
                                if (!starts.value || !endInput.value) return 0;
                                const ms = new Date(endInput.value) - new Date(starts.value);
                                return Math.max(Math.round(ms / 86400000), 0);
                            };

                            function render() {
                                const m = meta[select.value];
                                if (!m) return;

                                endWrap.hidden = !m.range;
                                endInput.required = m.range;
                                pets.max = m.maxPets;

                                const units = m.range ? Math.max(nights(), 1) : 1;
                                const extra = Math.max((parseInt(pets.value, 10) || 1) - 1, 0) * m.extra;
                                const total = units * (m.price + extra);

                                const ready = m.range ? (starts.value && endInput.value) : !!starts.value;
                                estimate.innerHTML = ready
                                    ? `<span class="label">${units} ${m.unit}${units > 1 ? 's' : ''}</span>
                                       <span class="price">RM ${total.toFixed(2)}</span>`
                                    : '<span class="meta">Choose your dates to see an estimate.</span>';
                            }

                            [select, starts, endInput, pets].forEach(el => {
                                el.addEventListener('change', render);
                                el.addEventListener('input', render);
                            });
                            render();
                        })();
                    </script>
                @endpush
            @else
                <p class="meta">Log in to send {{ $provider->user->name }} a booking request.</p>
                <a href="{{ route('login') }}" class="btn btn--accent btn--block" style="margin-top:1.25rem">Log in to book</a>
                <a href="{{ route('register') }}" class="btn btn--outline btn--block" style="margin-top:.6rem">Create an account</a>
            @endauth
        </div>
    @endif
</div>
