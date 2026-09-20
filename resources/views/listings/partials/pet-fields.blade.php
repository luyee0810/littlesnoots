{{-- Shared by listing create + edit. Expects $pet (may be a fresh model),
     $species, $breeds, $organizations. --}}
@php
    $v = fn ($key, $default = null) => old($key, $pet->{$key} ?? $default);
    $checked = fn ($key, $default = false) => (bool) old($key, $pet->{$key} ?? $default);
    // yes / no / unknown — null is a real answer, so compare loosely against ''.
    $tri = function ($key) use ($pet) {
        $value = old($key, $pet->{$key});
        return $value === null ? '' : ($value ? '1' : '0');
    };
    $hasOrg = (bool) old('organization_id', $pet->organization_id);
@endphp

<section class="panel">
    <div class="panel__head"><h2>The basics</h2></div>
    <div class="panel__body form-grid form-grid--2">
        <div class="field">
            <label for="name">Name <span aria-hidden="true">*</span></label>
            <input type="text" name="name" id="name" required maxlength="120" class="input"
                   value="{{ $v('name') }}" placeholder="e.g. Luna">
        </div>

        <div class="field">
            <label for="species_id">Species <span aria-hidden="true">*</span></label>
            <select name="species_id" id="species_id" required class="input">
                <option value="">Choose…</option>
                @foreach ($species as $s)
                    <option value="{{ $s->id }}" @selected($v('species_id') == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="field span-2">
            <label for="location">Where is this pet? </label>
            <input type="text" name="location" id="location" maxlength="160" class="input"
                   value="{{ $v('location') }}" placeholder="e.g. Bangsar, Kuala Lumpur">
            <p class="field-hint">A town or area is enough — never your full address.</p>
        </div>

        <div class="field span-2">
            <label for="description">Their story <span aria-hidden="true">*</span></label>
            <textarea name="description" id="description" rows="6" required maxlength="5000" class="textarea"
                      placeholder="How they came to need a home, their personality, what kind of family suits them.">{{ $v('description') }}</textarea>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel__head">
        <h2>Who’s rehoming</h2>
        <p class="panel__note">Optional — rescuers and fosterers can list without a shelter.</p>
    </div>
    <div class="panel__body form-grid">
        <fieldset class="fieldset">
            <legend class="sr-only">Rehoming on behalf of</legend>
            <label class="check">
                <input type="radio" name="_rehomer" value="me" @checked(! $hasOrg) data-org-toggle="hide">
                I’m rehoming this pet myself (rescuer, fosterer or owner)
            </label>
            <label class="check">
                <input type="radio" name="_rehomer" value="org" @checked($hasOrg) data-org-toggle="show">
                On behalf of a shelter or rescue organisation
            </label>
        </fieldset>

        <div class="field" id="organization-field" @if (! $hasOrg) hidden @endif>
            <label for="organization_id">Shelter or rescue</label>
            <select name="organization_id" id="organization_id" class="input">
                <option value="">None</option>
                @foreach ($organizations as $org)
                    <option value="{{ $org->id }}" @selected($v('organization_id') == $org->id)>{{ $org->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel__head"><h2>Breed</h2></div>
    <div class="panel__body form-grid form-grid--2">
        <div class="field">
            <label for="breed_id">Primary breed</label>
            <select name="breed_id" id="breed_id" class="input" data-breed-select>
                <option value="">Not sure</option>
                @foreach ($breeds as $breed)
                    <option value="{{ $breed->id }}" data-species="{{ $breed->species_id }}"
                            @selected($v('breed_id') == $breed->id)>{{ $breed->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="secondary_breed_id">Second breed</label>
            <select name="secondary_breed_id" id="secondary_breed_id" class="input" data-breed-select>
                <option value="">None</option>
                @foreach ($breeds as $breed)
                    <option value="{{ $breed->id }}" data-species="{{ $breed->species_id }}"
                            @selected($v('secondary_breed_id') == $breed->id)>{{ $breed->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="field span-2">
            <div class="chip-row" style="gap:1.25rem">
                <label class="check">
                    <input type="checkbox" name="breed_mixed" value="1" @checked($checked('breed_mixed'))> Mixed breed
                </label>
                <label class="check">
                    <input type="checkbox" name="breed_unknown" value="1" @checked($checked('breed_unknown'))> Breed unknown
                </label>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel__head"><h2>Characteristics</h2></div>
    <div class="panel__body form-grid form-grid--2">
        <div class="field">
            <label for="age_group">Age <span aria-hidden="true">*</span></label>
            <select name="age_group" id="age_group" required class="input">
                @foreach (['baby' => 'Baby', 'young' => 'Young', 'adult' => 'Adult', 'senior' => 'Senior'] as $key => $label)
                    <option value="{{ $key }}" @selected($v('age_group') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="age_months">Age in months</label>
            <input type="number" name="age_months" id="age_months" min="0" max="360" class="input"
                   value="{{ $v('age_months') }}" placeholder="Optional">
        </div>

        <div class="field">
            <label for="gender">Gender <span aria-hidden="true">*</span></label>
            <select name="gender" id="gender" required class="input">
                @foreach (['male' => 'Male', 'female' => 'Female', 'unknown' => 'Unknown'] as $key => $label)
                    <option value="{{ $key }}" @selected($v('gender') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="size">Size <span aria-hidden="true">*</span></label>
            <select name="size" id="size" required class="input">
                @foreach (['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large', 'xlarge' => 'Extra large'] as $key => $label)
                    <option value="{{ $key }}" @selected($v('size') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="coat">Coat</label>
            <select name="coat" id="coat" class="input">
                <option value="">Not specified</option>
                @foreach (['hairless' => 'Hairless', 'short' => 'Short', 'medium' => 'Medium', 'long' => 'Long', 'wire' => 'Wire', 'curly' => 'Curly'] as $key => $label)
                    <option value="{{ $key }}" @selected($v('coat') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="color">Colour</label>
            <input type="text" name="color" id="color" maxlength="60" class="input"
                   value="{{ $v('color') }}" placeholder="e.g. Tabby">
        </div>

        <div class="field">
            <label for="secondary_color">Second colour</label>
            <input type="text" name="secondary_color" id="secondary_color" maxlength="60" class="input"
                   value="{{ $v('secondary_color') }}">
        </div>

        <div class="field">
            <label for="adoption_fee">Adoption fee (RM)</label>
            <input type="number" name="adoption_fee" id="adoption_fee" min="0" step="0.01" class="input"
                   value="{{ $v('adoption_fee') }}" placeholder="0.00">
            <p class="field-hint">Leave blank if there’s no fee.</p>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel__head"><h2>Health &amp; care</h2></div>
    <div class="panel__body form-grid">
        <div class="chip-row" style="gap:1.25rem;flex-wrap:wrap">
            @foreach ([
                'spayed_neutered' => 'Spayed / neutered',
                'shots_current' => 'Vaccinations up to date',
                'house_trained' => 'House trained',
                'declawed' => 'Declawed',
                'special_needs' => 'Has special needs',
            ] as $key => $label)
                <label class="check">
                    <input type="checkbox" name="{{ $key }}" value="1" @checked($checked($key))> {{ $label }}
                </label>
            @endforeach
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel__head">
        <h2>Good in a home with</h2>
        <p class="panel__note">Leave as “Not sure” if you haven’t seen them with one.</p>
    </div>
    <div class="panel__body form-grid form-grid--2">
        @foreach ([
            'good_with_children' => 'Children',
            'good_with_dogs' => 'Dogs',
            'good_with_cats' => 'Cats',
        ] as $key => $label)
            <div class="field">
                <label for="{{ $key }}">{{ $label }}</label>
                <select name="{{ $key }}" id="{{ $key }}" class="input">
                    <option value="" @selected($tri($key) === '')>Not sure</option>
                    <option value="1" @selected($tri($key) === '1')>Yes</option>
                    <option value="0" @selected($tri($key) === '0')>No</option>
                </select>
            </div>
        @endforeach
    </div>
</section>

<section class="panel">
    <div class="panel__head"><h2>Status</h2></div>
    <div class="panel__body form-grid form-grid--2">
        <div class="field">
            <label for="status">Current status</label>
            <select name="status" id="status" required class="input">
                @foreach ([
                    'available' => 'Available for adoption',
                    'pending' => 'Adoption pending',
                    'adopted' => 'Adopted',
                    'found' => 'Found / reunited',
                    'unavailable' => 'Not available',
                ] as $key => $label)
                    <option value="{{ $key }}" @selected($v('status', 'available') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</section>
