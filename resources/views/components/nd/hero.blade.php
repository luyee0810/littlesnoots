{{-- Marquee Hero (H2 split) + stat bar.
     Pet images are placeholders — swap for transparent cut-out PNGs of real pets. --}}
<section class="hero section">
  <svg class="doodle hide-sm" style="top:13%;left:27%;width:26px;color:var(--coral)" viewBox="0 0 24 24"><use href="#i-heart-o"/></svg>
  <svg class="doodle hide-sm" style="top:17%;left:35%;width:26px;transform:rotate(-12deg);color:oklch(68% 0.12 250)" viewBox="0 0 24 24"><use href="#i-paw"/></svg>
  <div class="wrap">
    <div class="hero-grid">
      <div class="hero-copy">
        <span class="hero-badge"><svg style="width:14px" viewBox="0 0 24 24"><use href="#i-paw"/></svg>Adopt. Love. Thrive.</span>
        <h1>Find the companion who changes
          <span class="hi-em">everything.
            <svg class="u u1" viewBox="0 0 200 12" preserveAspectRatio="none"><path d="M4 7c50-6 140-6 192-1" fill="none" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/></svg>
            <svg class="u u2" viewBox="0 0 200 12" preserveAspectRatio="none"><path d="M3 6c55-5 135-4 194 1" fill="none" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/></svg>
          </span>
        </h1>
        <p class="hero-sub">Thousands of amazing pets are waiting for a second chance and a forever home. Adopt love, make a difference.</p>
        <div class="hero-cta">
          <a href="{{ route('pets.index') }}" class="btn btn-green">Adopt a Pet <svg><use href="#i-paw"/></svg></a>
          <a href="{{ route('newdesign') }}#how" class="btn btn-ghost">How It Works
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m10 9 5 3-5 3Z" fill="currentColor"/></svg>
          </a>
        </div>
        <div class="adopters">
          <div class="avatars">
            <img src="https://i.pravatar.cc/88?img=32" alt="">
            <img src="https://i.pravatar.cc/88?img=12" alt="">
            <img src="https://i.pravatar.cc/88?img=45" alt="">
            <img src="https://i.pravatar.cc/88?img=5" alt="">
          </div>
          <span>Join <b>120K+</b> happy adopters</span>
        </div>
      </div>

      <div class="hero-art">
        <div class="blob"></div>
        <div class="blob-2"></div>
        <div class="pets">
          <img class="pet-cut pet-dog" src="https://placedog.net/540/680?id=170" alt="A friendly golden dog looking for a home">
          <div class="cat-wrap">
            <svg class="crown" viewBox="0 0 24 24"><use href="#i-crown"/></svg>
            <img class="pet-cut pet-cat" src="https://cataas.com/cat?width=360&height=450&type=square" alt="A curious tabby cat">
          </div>
        </div>
        <div class="speech">Best decision, ever!</div>
        <div class="card-stack">
          <div class="float-card">
            <span class="seal" style="color:var(--p-blue)"><svg class="seal-bg"><use href="#i-seal"/></svg><svg class="seal-ic" style="color:var(--green)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z"/><path d="m9 12 2 2 4-4" stroke-linecap="round"/></svg></span>
            <div><strong>Verified Shelters</strong><small>100% Trusted &amp; Verified</small></div>
          </div>
          <div class="float-card">
            <span class="seal" style="color:var(--p-pink)"><svg class="seal-bg"><use href="#i-seal"/></svg><svg class="seal-ic" style="color:var(--coral)" viewBox="0 0 24 24"><use href="#i-paw"/></svg></span>
            <div><strong>Successful Adoptions</strong><small>20,000+</small></div>
          </div>
          <div class="float-card">
            <span class="seal" style="color:var(--p-yellow)"><svg class="seal-bg"><use href="#i-seal"/></svg><svg class="seal-ic" style="color:var(--amber-deep)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            <div><strong>Happy Pets Every Day</strong><small>24/7</small></div>
          </div>
        </div>
        <svg class="doodle green float hide-sm" style="top:6%;right:8%;width:28px;--r:0" viewBox="0 0 24 24"><use href="#i-spark"/></svg>
        <svg class="doodle amber float hide-sm" style="bottom:12%;right:4%;width:26px;--r:-8deg" viewBox="0 0 24 24"><use href="#i-star"/></svg>
        <svg class="doodle hide-sm" style="bottom:20%;right:14%;width:22px;color:oklch(68% 0.12 250)" viewBox="0 0 24 24"><use href="#i-spark"/></svg>
      </div>
    </div>

    {{-- stat bar --}}
    <div class="stats reveal">
      <div class="stat"><span class="ic" style="background:var(--p-pink)"><svg style="width:22px;color:var(--coral)" viewBox="0 0 24 24"><use href="#i-paw"/></svg></span><div><b>12,000+</b><small>Pets Available</small></div></div>
      <div class="stat"><span class="ic" style="background:var(--p-green)"><svg style="width:22px;color:var(--green)" viewBox="0 0 24 24"><use href="#i-heart"/></svg></span><div><b>20,000+</b><small>Successful Adoptions</small></div></div>
      <div class="stat"><span class="ic" style="background:var(--p-yellow)"><svg style="width:22px;color:var(--amber-deep)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11 12 4l9 7v8a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1Z"/></svg></span><div><b>980+</b><small>Shelters &amp; Rescues</small></div></div>
      <div class="stat"><span class="ic" style="background:var(--p-blue)"><svg style="width:22px;color:var(--green)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z"/></svg></span><div><b>100%</b><small>Verified &amp; Safe</small></div></div>
    </div>
  </div>
</section>
