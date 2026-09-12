@extends('layouts.app')

@section('title', 'Create a memorial — Little Snoots')

@php($v = fn ($key, $default = null) => old($key, $default))

@section('content')
    <div class="shell-narrow page">
        <div class="page-head">
            <div>
                <p class="kicker">In loving memory</p>
                <h1>Create a memorial</h1>
                <p class="lede">Honour a pet who has passed with a lasting tribute page.</p>
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

        <form method="POST" action="{{ route('memorials.store') }}" enctype="multipart/form-data"
              class="form-grid" style="margin-top:2rem">
            @csrf

            <section class="panel">
                <div class="panel__head"><h2>About them</h2></div>
                <div class="panel__body form-grid">
                    <div class="field">
                        <label for="pet_name">Their name <span aria-hidden="true">*</span></label>
                        <input type="text" name="pet_name" id="pet_name" required maxlength="255"
                               class="input" value="{{ $v('pet_name') }}" placeholder="e.g. Whiskers">
                    </div>

                    <div class="field">
                        <label for="species">Species or breed</label>
                        <input type="text" name="species" id="species" maxlength="255"
                               class="input" value="{{ $v('species') }}" placeholder="e.g. Cat, Golden Retriever">
                    </div>

                    <div class="form-grid form-grid--2">
                        <div class="field">
                            <label for="born_on">Date of birth</label>
                            <input type="date" name="born_on" id="born_on" class="input"
                                   max="{{ now()->toDateString() }}" value="{{ $v('born_on') }}">
                        </div>
                        <div class="field">
                            <label for="passed_on">Date of passing</label>
                            <input type="date" name="passed_on" id="passed_on" class="input"
                                   max="{{ now()->toDateString() }}" value="{{ $v('passed_on') }}">
                        </div>
                    </div>

                    <div class="field">
                        <label for="photo">A photo</label>
                        <input type="file" name="photo" id="photo" accept="image/*" class="input">
                        <p class="field-hint">Optional. JPG or PNG, up to 4&nbsp;MB.</p>
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel__head"><h2>Your tribute</h2></div>
                <div class="panel__body form-grid">
                    <div class="field">
                        <label for="tribute">A few words in their memory <span aria-hidden="true">*</span></label>
                        <textarea name="tribute" id="tribute" rows="8" required maxlength="5000" class="textarea"
                                  placeholder="Share your favourite memories, their little quirks, and what they meant to you.">{{ $v('tribute') }}</textarea>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <a href="{{ route('memorials.index') }}" class="btn btn--ghost btn--sm">Cancel</a>
                <button type="submit" class="btn btn--accent">Publish memorial</button>
            </div>
        </form>
    </div>
@endsection
