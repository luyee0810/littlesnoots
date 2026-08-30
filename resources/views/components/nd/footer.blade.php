@php
    $cols = [
        'Explore'   => ['Adopt', 'Services', 'Success Stories', 'Resources', 'Blog'],
        'Support'   => ['Help Center', 'Adoption Process', 'Shelter Partners', 'FAQ'],
        'Resources' => ['Guides', 'Training & Tips', 'Health & Care', 'Pet Calculator'],
        'Company'   => ['About Us', 'Our Team', 'Careers', 'Contact Us'],
    ];
@endphp

<footer class="foot">
  <div class="wrap">
    <div class="foot-grid">
      <div class="brandcol">
        <a href="{{ route('newdesign') }}" class="brand">
          <svg class="paw" aria-hidden="true"><use href="#i-paw"/></svg>Two Fat Cats
        </a>
        <p class="tagline">Connecting loving pets with loving people. Because every pet deserves a home.</p>
        <div class="socials">
          <a href="#" aria-label="Two Fat Cats on Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="3.5"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg></a>
          <a href="#" aria-label="Two Fat Cats on Facebook"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 8h2V5h-2c-2 0-3 1.3-3 3v2H9v3h2v6h3v-6h2l1-3h-3V8.5c0-.3.2-.5.5-.5H14Z"/></svg></a>
          <a href="#" aria-label="Two Fat Cats on YouTube"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12s0-3-.4-4.3a2.2 2.2 0 0 0-1.5-1.5C18.7 6 12 6 12 6s-6.7 0-8.1.2a2.2 2.2 0 0 0-1.5 1.5C2 9 2 12 2 12s0 3 .4 4.3c.2.8.8 1.3 1.5 1.5C5.3 18 12 18 12 18s6.7 0 8.1-.2c.7-.2 1.3-.7 1.5-1.5C22 15 22 12 22 12Zm-12 2.5v-5l4.5 2.5-4.5 2.5Z"/></svg></a>
          <a href="#" aria-label="Two Fat Cats on TikTok"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 3c.3 2 1.6 3.5 3.5 3.8V10c-1.4 0-2.6-.4-3.5-1v6.2A5.2 5.2 0 1 1 10.8 10v3.1a2.2 2.2 0 1 0 1.7 2.1V3H16Z"/></svg></a>
        </div>
      </div>

      @foreach ($cols as $heading => $links)
        <nav class="fcol" aria-labelledby="f-{{ Str::slug($heading) }}">
          <h4 id="f-{{ Str::slug($heading) }}">{{ $heading }}</h4>
          @foreach ($links as $l)
            <a href="{{ route('pets.index') }}">{{ $l }}</a>
          @endforeach
        </nav>
      @endforeach

      <div class="newsletter">
        <h4>Stay in the loop</h4>
        <p>Get heartwarming stories and adoption updates.</p>
        {{-- Visible label: a placeholder alone is not an accessible label. --}}
        <form class="news-form-wrap" onsubmit="return false">
          <label class="news-label" for="nd-news">Email address</label>
          <div class="news-form">
            <input id="nd-news" type="email" name="email" autocomplete="email" placeholder="you@example.com">
            <button type="submit" aria-label="Subscribe to the newsletter">
              <svg style="width:16px" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="copyright">
      <span>&copy; {{ date('Y') }} Two Fat Cats. All rights reserved.</span>
      <span>Design preview &middot; some links are placeholders</span>
    </div>
  </div>
</footer>
