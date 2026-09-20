@extends('layouts.app')

@php($isNew = ! $organization->exists)
@section('title', ($isNew ? 'Add a shelter' : $organization->name).' — Admin')

@php($v = fn ($key, $default = null) => old($key, $organization->{$key} ?? $default))
@php($days = ['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'])

@section('content')
    <div class="shell-mid page">
        @include('admin.partials.nav')

        <div class="page-head" style="margin-top:1.5rem">
            <div>
                <p class="kicker"><a href="{{ route('admin.organizations.index') }}">← All shelters</a></p>
                <h1>{{ $isNew ? 'Add a shelter' : $organization->name }}</h1>
                @unless ($isNew)
                    <p class="lede" style="margin-top:.35rem">
                        {{ $organization->pets()->count() }} {{ Str::plural('listing', $organization->pets()->count()) }}
                    </p>
                @endunless
            </div>
        </div>

        @include('listings.partials.errors')

        <form method="POST"
              action="{{ $isNew ? route('admin.organizations.store') : route('admin.organizations.update', $organization) }}"
              class="form-grid" style="margin-top:2rem">
            @csrf
            @unless ($isNew) @method('PUT') @endunless

            <section class="panel">
                <div class="panel__head"><h2>Identity</h2></div>
                <div class="panel__body form-grid form-grid--2">
                    <div class="field">
                        <label for="name">Name <span aria-hidden="true">*</span></label>
                        <input type="text" name="name" id="name" required maxlength="160" class="input" value="{{ $v('name') }}">
                    </div>
                    <div class="field">
                        <label for="type">Type</label>
                        <select name="type" id="type" class="input">
                            @foreach (['shelter' => 'Shelter', 'rescue' => 'Rescue', 'foster' => 'Foster network', 'individual' => 'Individual'] as $key => $label)
                                <option value="{{ $key }}" @selected($v('type', 'shelter') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="input" value="{{ $v('email') }}">
                    </div>
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input type="text" name="phone" id="phone" maxlength="40" class="input" value="{{ $v('phone') }}">
                    </div>
                    <div class="field span-2">
                        <label for="website">Website</label>
                        <input type="url" name="website" id="website" class="input" value="{{ $v('website') }}" placeholder="https://">
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel__head"><h2>Where they are</h2></div>
                <div class="panel__body form-grid form-grid--2">
                    <div class="field span-2">
                        <label for="address1">Address</label>
                        <input type="text" name="address1" id="address1" class="input" value="{{ $v('address1') }}">
                    </div>
                    <div class="field">
                        <label for="city">Town / city</label>
                        <input type="text" name="city" id="city" class="input" value="{{ $v('city') }}">
                    </div>
                    <div class="field">
                        <label for="state">State</label>
                        <input type="text" name="state" id="state" class="input" value="{{ $v('state') }}">
                    </div>
                    <div class="field">
                        <label for="postcode">Postcode</label>
                        <input type="text" name="postcode" id="postcode" class="input" value="{{ $v('postcode') }}">
                    </div>
                    <div class="field">
                        <label for="country">Country</label>
                        <input type="text" name="country" id="country" maxlength="2" required class="input"
                               value="{{ $v('country', 'MY') }}">
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel__head">
                    <h2>Opening hours</h2>
                    <p class="panel__note">Leave a day blank to omit it.</p>
                </div>
                <div class="panel__body form-grid form-grid--2">
                    @foreach ($days as $key => $label)
                        <div class="field">
                            <label for="hours-{{ $key }}">{{ $label }}</label>
                            <input type="text" name="hours[{{ $key }}]" id="hours-{{ $key }}" maxlength="40" class="input"
                                   value="{{ old("hours.$key", $organization->hours[$key] ?? '') }}"
                                   placeholder="10:00–18:00">
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="panel">
                <div class="panel__head"><h2>About &amp; policy</h2></div>
                <div class="panel__body form-grid">
                    <div class="field">
                        <label for="mission_statement">Mission</label>
                        <textarea name="mission_statement" id="mission_statement" rows="3" maxlength="2000" class="textarea">{{ $v('mission_statement') }}</textarea>
                    </div>
                    <div class="field">
                        <label for="adoption_policy">Adoption policy</label>
                        <textarea name="adoption_policy" id="adoption_policy" rows="3" maxlength="2000" class="textarea">{{ $v('adoption_policy') }}</textarea>
                        <p class="field-hint">Shown on every listing from this shelter.</p>
                    </div>
                    <div class="form-grid form-grid--2">
                        <div class="field">
                            <label for="facebook">Facebook</label>
                            <input type="url" name="facebook" id="facebook" class="input" value="{{ $v('facebook') }}" placeholder="https://">
                        </div>
                        <div class="field">
                            <label for="instagram">Instagram</label>
                            <input type="url" name="instagram" id="instagram" class="input" value="{{ $v('instagram') }}" placeholder="https://">
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-between gap-4">
                @unless ($isNew)
                    <button type="submit" form="delete-organization" class="btn btn--ghost btn--sm">Delete shelter</button>
                @endunless
                <button type="submit" class="btn btn--accent">{{ $isNew ? 'Add shelter' : 'Save changes' }}</button>
            </div>
        </form>

        @unless ($isNew)
            <form method="POST" action="{{ route('admin.organizations.destroy', $organization) }}" id="delete-organization"
                  onsubmit="return confirm('Remove this shelter? Its listings are kept and simply lose the shelter link.')">
                @csrf @method('DELETE')
            </form>
        @endunless
    </div>
@endsection
