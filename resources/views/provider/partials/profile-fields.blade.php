{{-- Shared by onboarding and profile editing. Expects $profile (may be a fresh model). --}}
@php
    $v = fn ($key, $default = null) => old($key, $profile->{$key} ?? $default);
    $checked = fn ($key, $default = false) => (bool) old($key, $profile->{$key} ?? $default);
    $inArr = fn ($key, $value) => in_array($value, (array) old($key, $profile->{$key} ?? []), true);
@endphp

<section class="panel">
    <div class="panel__head"><h2>About you</h2></div>
    <div class="panel__body form-grid">
        <div class="field">
            <label for="headline">Headline</label>
            <input type="text" name="headline" id="headline" required maxlength="255" class="input"
                   value="{{ $v('headline') }}" placeholder="Cat-obsessed boarder with a quiet spare room">
            <p class="field-hint">One line that sums up what you offer.</p>
        </div>
        <div class="field">
            <label for="bio">About</label>
            <textarea name="bio" id="bio" rows="5" class="textarea"
                      placeholder="Tell owners about your experience, your home, and how you care for pets.">{{ $v('bio') }}</textarea>
        </div>
        <div class="field" style="max-width:10rem">
            <label for="years_experience">Years of experience</label>
            <input type="number" name="years_experience" id="years_experience" required min="0" max="60"
                   class="input" value="{{ $v('years_experience', 0) }}">
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel__head"><h2>Where you are</h2></div>
    <div class="panel__body form-grid form-grid--2">
        <div class="field span-2">
            <label for="address1">Address</label>
            <input type="text" name="address1" id="address1" class="input" value="{{ $v('address1') }}"
                   placeholder="12 Jalan Bangsar">
            <p class="field-hint">Never shown publicly — only your town is.</p>
        </div>
        <div class="field">
            <label for="city">Town / city</label>
            <input type="text" name="city" id="city" required class="input" value="{{ $v('city') }}" placeholder="Petaling Jaya">
        </div>
        <div class="field">
            <label for="state">State</label>
            <input type="text" name="state" id="state" class="input" value="{{ $v('state') }}" placeholder="Selangor">
        </div>
        <div class="field">
            <label for="postcode">Postcode</label>
            <input type="text" name="postcode" id="postcode" class="input" value="{{ $v('postcode') }}" placeholder="46000">
        </div>
        <div class="field">
            <label for="service_radius_km">Travel radius (km)</label>
            <input type="number" name="service_radius_km" id="service_radius_km" required min="1" max="100"
                   class="input" value="{{ $v('service_radius_km', 10) }}">
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel__head"><h2>Which pets you take</h2></div>
    <div class="panel__body form-grid">
        <fieldset class="fieldset">
            <legend>Species</legend>
            <div class="chip-row" style="gap:1.25rem">
                @foreach ($species as $s)
                    <label class="check">
                        <input type="checkbox" name="accepts_species[]" value="{{ $s->slug }}"
                               @checked($inArr('accepts_species', $s->slug))>
                        {{ $s->name }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="fieldset">
            <legend>Sizes</legend>
            <div class="chip-row" style="gap:1.25rem">
                @foreach (['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'] as $key => $label)
                    <label class="check">
                        <input type="checkbox" name="accepts_sizes[]" value="{{ $key }}"
                               @checked($inArr('accepts_sizes', $key))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="fieldset">
            <legend>Days you work</legend>
            <div class="chip-row" style="gap:1.25rem">
                @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                    <label class="check">
                        <input type="checkbox" name="available_days[]" value="{{ $day }}"
                               @checked($inArr('available_days', $day))>
                        {{ $day }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="field" style="max-width:10rem">
            <label for="max_pets_per_booking">Max pets per booking</label>
            <input type="number" name="max_pets_per_booking" id="max_pets_per_booking" required min="1" max="10"
                   class="input" value="{{ $v('max_pets_per_booking', 2) }}">
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel__head"><h2>Your home</h2></div>
    <div class="panel__body form-grid">
        <div class="field" style="max-width:16rem">
            <label for="home_type">Home type</label>
            <select name="home_type" id="home_type" class="select">
                <option value="">Select…</option>
                @foreach (['condominium', 'apartment', 'landed house', 'farm'] as $type)
                    <option value="{{ $type }}" @selected($v('home_type') === $type)>{{ ucfirst($type) }}</option>
                @endforeach
            </select>
        </div>

        <div class="check-stack">
            @foreach ([
                'has_fenced_yard' => 'I have a fenced yard',
                'has_own_pets' => 'I have pets of my own',
                'is_smoke_free' => 'My home is smoke-free',
                'has_insurance' => 'I hold pet-care insurance',
            ] as $field => $label)
                <label class="check">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input type="checkbox" name="{{ $field }}" value="1" @checked($checked($field))>
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </div>
</section>
