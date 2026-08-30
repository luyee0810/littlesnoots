@php
    $reviews = [
        ['q' => 'Two Fat Cats made adopting so easy and stress-free!',      'who' => 'Micheal & Lilo'],
        ['q' => 'We found our best friend thanks to this wonderful team.',  'who' => 'Aura & Paw'],
        ['q' => 'The team is so supportive and genuinely caring.',          'who' => 'Zashie & Bean'],
    ];
@endphp

<section class="section" aria-labelledby="loved-h">
  <div class="wrap">
    <div class="loved reveal">
      <svg class="doodle hide-sm" style="top:12%;right:6%;width:26px;color:var(--amber)"
           viewBox="0 0 24 24" aria-hidden="true"><use href="#i-spark"/></svg>
      <div class="loved-grid">
        <div class="intro">
          <h2 id="loved-h">Loved by Pets and People</h2>
          <p>Real stories from our amazing adoption community.</p>
        </div>
        <div class="reviews">
          @foreach ($reviews as $r)
            <figure class="review">
              {{-- Glyphs are decorative; the rating is announced as text. --}}
              <div class="stars"><span aria-hidden="true">★★★★★</span><span class="sr-only">Rated 5 out of 5</span></div>
              <blockquote><p>{{ $r['q'] }}</p></blockquote>
              <figcaption class="who">— {{ $r['who'] }}</figcaption>
            </figure>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
