@extends('layouts.app')

@php
    $editing = $service->exists;
    $v = fn ($key, $default = null) => old($key, $service->{$key} ?? $default);
@endphp

@section('title', ($editing ? 'Edit service' : 'Add a service').' — Little Snoots')

@section('content')
    <div class="shell-narrow page">
        @include('provider.partials.nav', ['active' => 'services'])

        <div class="page-head" style="margin-top:2rem">
            <div>
                <h1>{{ $editing ? 'Edit service' : 'Add a service' }}</h1>
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

        @if ($categories->isEmpty() && ! $editing)
            <div class="empty" style="margin-top:2rem">
                <p class="empty__icon">✅</p>
                <h2>You already offer every service we list</h2>
                <p>Edit an existing one instead.</p>
                <a href="{{ route('provider.services.index') }}" class="btn btn--outline btn--sm">Back to services</a>
            </div>
        @else
            <form method="POST" class="form-grid" style="margin-top:2rem"
                  action="{{ $editing ? route('provider.services.update', $service) : route('provider.services.store') }}">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <section class="panel">
                    <div class="panel__head"><h2>What you offer</h2></div>
                    <div class="panel__body form-grid">
                        <div class="field">
                            <label for="service_category_id">Service</label>
                            <select name="service_category_id" id="service_category_id" required class="select">
                                @if ($editing)
                                    <option value="{{ $service->service_category_id }}" selected>{{ $service->category->name }}</option>
                                @endif
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-unit="{{ $category->pricing_unit }}"
                                            @selected((int) $v('service_category_id') === $category->id)>
                                        {{ $category->name }} (priced {{ $category->priceSuffix() }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="title">Custom title</label>
                            <input type="text" name="title" id="title" value="{{ $v('title') }}" maxlength="255" class="input"
                                   placeholder="Optional — defaults to the service name">
                        </div>

                        <div class="field">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="3" class="textarea"
                                      placeholder="What’s included, and how you run it.">{{ $v('description') }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel__head"><h2>Rates &amp; limits</h2></div>
                    <div class="panel__body form-grid form-grid--2">
                        <div class="field">
                            <label for="price">Your rate (RM)</label>
                            <input type="number" name="price" id="price" required min="1" step="1" class="input" value="{{ $v('price') }}">
                            <p class="field-hint">Per {{ $editing ? $service->price_unit : 'unit for this service' }}, covering one pet.</p>
                        </div>
                        <div class="field">
                            <label for="additional_pet_price">Extra pet (RM)</label>
                            <input type="number" name="additional_pet_price" id="additional_pet_price" min="0" step="1"
                                   class="input" value="{{ $v('additional_pet_price') }}" placeholder="Optional">
                        </div>
                        <div class="field">
                            <label for="min_units">Minimum booking</label>
                            <input type="number" name="min_units" id="min_units" required min="1" max="365"
                                   class="input" value="{{ $v('min_units', 1) }}">
                        </div>
                        <div class="field">
                            <label for="max_pets">Max pets</label>
                            <input type="number" name="max_pets" id="max_pets" required min="1" max="10"
                                   class="input" value="{{ $v('max_pets', 2) }}">
                        </div>
                        <label class="check span-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked((bool) $v('is_active', true))>
                            Show this service on my profile
                        </label>
                    </div>
                </section>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('provider.services.index') }}" class="btn btn--ghost btn--sm">Cancel</a>
                    <button type="submit" class="btn btn--accent">{{ $editing ? 'Save changes' : 'Add service' }}</button>
                </div>
            </form>
        @endif
    </div>
@endsection
