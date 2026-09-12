{{-- N6 masthead: wordmark · centered links · search / wishlist / Donate --}}
<header class="nav">
  <div class="wrap nav-in">
    <a href="{{ route('home') }}" class="brand"><svg class="paw"><use href="#i-paw"/></svg>Little Snoots</a>
    <nav class="nav-links" aria-label="Primary">
      <a href="{{ route('newdesign') }}" class="active">Home</a>
      <a href="{{ route('pets.index') }}">Adopt</a>
      <a href="{{ route('services.index') }}">Services</a>
      <a href="{{ route('newdesign') }}#stories">Success Stories</a>
      <a href="{{ route('newdesign') }}#guides">Resources</a>
      <a href="{{ route('newdesign') }}#about">About Us</a>
    </nav>
    <div class="nav-right">
      <button class="icon-btn" aria-label="Search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg></button>
      <a href="{{ route('newdesign') }}#wishlist" class="icon-btn" aria-label="Wishlist"><svg><use href="#i-heart-o"/></svg></a>
      <a href="{{ route('newdesign') }}#donate" class="btn btn-tomato">Donate</a>
      <button class="icon-btn nav-toggle" aria-label="Menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
    </div>
  </div>
</header>
