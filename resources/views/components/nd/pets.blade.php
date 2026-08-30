@props(['pets' => collect()])

{{-- Tag tint rotation — keeps the scrapbook colour rhythm without hardcoding
     a colour per pet, since tags come from the database. --}}
@php
    $tints = [
        ['bg' => 'var(--p-pink)',   'fg' => 'var(--coral-deep)'],
        ['bg' => 'var(--p-green)',  'fg' => 'var(--green)'],
        ['bg' => 'var(--p-blue)',   'fg' => 'var(--green)'],
        ['bg' => 'var(--p-yellow)', 'fg' => 'var(--amber-deep)'],
    ];
@endphp

<section class="section" id="pets" aria-labelledby="pets-h">
  <div class="wrap">
    <div class="head reveal">
      <h2 id="pets-h">Meet Your<br>New Best Friend
        <svg class="heart" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-heart-o"/></svg>
      </h2>
      <a href="{{ route('pets.index') }}" class="btn btn-ghost">View all pets
        <svg aria-hidden="true"><use href="#i-arrow"/></svg>
      </a>
    </div>

    @if ($pets->isEmpty())
      <p class="pmeta">No pets are listed just yet — check back soon.</p>
    @else
      <div class="pet-cards">
        @foreach ($pets as $i => $pet)
          @php($photo = $pet->primaryPhoto())
          <article class="pcard reveal" @if($i) style="transition-delay:{{ $i * .08 }}s" @endif>
            <div class="photo">
              @if ($photo)
                <img src="{{ $photo->url() }}"
                     alt="{{ $photo->alt ?? 'Photo of '.$pet->name }}" loading="lazy">
              @else
                <div class="photo-fallback" role="img" aria-label="No photo of {{ $pet->name }} yet">
                  <svg viewBox="0 0 24 24" aria-hidden="true"><use href="#i-paw"/></svg>
                </div>
              @endif
              {{-- Decorative until a wishlist feature exists — not a control. --}}
              <span class="fav" aria-hidden="true">
                <svg style="width:16px" viewBox="0 0 24 24"><use href="#i-heart-o"/></svg>
              </span>
            </div>

            <div class="body">
              <div class="pname">
                <h3>{{ $pet->name }}</h3>
                <span class="g">{{ strtolower((string) $pet->gender) === 'female' ? '♀' : '♂' }}
                  <span class="sr-only">{{ ucfirst((string) $pet->gender) }}</span>
                </span>
              </div>

              <div class="pmeta">
                <span>{{ $pet->ageForHumans() ?? 'Age on request' }}</span>
                <span class="dot" aria-hidden="true"></span>
                <span>{{ $pet->breedLabel() }}</span>
                @if ($pet->location)
                  <span class="dot" aria-hidden="true"></span>
                  <span>{{ $pet->location }}</span>
                @endif
              </div>

              @if (!empty($pet->tags))
                <div class="tags">
                  @foreach (array_slice($pet->tags, 0, 2) as $j => $tag)
                    @php($t = $tints[($i + $j) % count($tints)])
                    <span class="tag" style="background:{{ $t['bg'] }};color:{{ $t['fg'] }}">{{ $tag }}</span>
                  @endforeach
                </div>
              @endif

              <a href="{{ route('pets.show', $pet) }}" class="btn btn-green">Meet {{ $pet->name }}
                <svg aria-hidden="true"><use href="#i-paw"/></svg>
              </a>
            </div>
          </article>
        @endforeach
      </div>
    @endif
  </div>
</section>
