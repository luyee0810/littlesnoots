@extends('layouts.app')

@section('title', 'List a pet for adoption — Little Snoots')

@section('content')
    <div class="shell-mid page">
        <div class="page-head">
            <div>
                <p class="kicker">Rehoming</p>
                <h1>List a pet for adoption</h1>
                <p class="lede" style="margin-top:.75rem">
                    Whether you’re a shelter, a rescuer or fostering one cat — tell us about them.
                    We’ll check the listing over before it goes live.
                </p>
            </div>
        </div>

        <div class="steps" style="margin-top:1.75rem">
            <span class="step" data-current><span class="step__n">1</span> About the pet</span>
            <span class="step"><span class="step__n">2</span> Photos</span>
            <span class="step"><span class="step__n">3</span> Review</span>
        </div>

        @include('listings.partials.errors')

        <form method="POST" action="{{ route('listings.store') }}" enctype="multipart/form-data"
              class="form-grid" style="margin-top:2rem">
            @csrf

            @include('listings.partials.pet-fields')

            <section class="panel">
                <div class="panel__head"><h2>Photos</h2></div>
                <div class="panel__body form-grid">
                    <div class="field">
                        <label for="photos">Upload photos</label>
                        <input type="file" name="photos[]" id="photos" class="input" multiple
                               accept="image/jpeg,image/png,image/webp">
                        <p class="field-hint">
                            Up to 8 photos, 8&nbsp;MB each. The first one becomes the main photo —
                            you can change that afterwards.
                        </p>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="field-hint">Saved as a draft — nothing goes public until you send it for review.</p>
                <button type="submit" class="btn btn--accent">
                    Save draft <i data-lucide="arrow-right" aria-hidden="true"></i>
                </button>
            </div>
        </form>
    </div>

    @include('listings.partials.form-script')
@endsection
