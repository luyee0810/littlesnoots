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
          <span class="dog-cut eye-cut" data-eyes data-head>
            <img class="pet-cut pet-dog is-cutout" src="{{ asset('images/hero/dog-cake.png') }}" alt="A happy black-and-white rescue dog looking at the camera">
            <span class="eye" data-ex="34" data-ey="23"><img class="eyeball" src="{{ asset('images/hero/dog-cake.png') }}" alt="" aria-hidden="true"><span class="lid"></span></span>
            <span class="eye" data-ex="67" data-ey="24"><img class="eyeball" src="{{ asset('images/hero/dog-cake.png') }}" alt="" aria-hidden="true"><span class="lid"></span></span>
          </span>
          <span class="cat-cut eye-cut" data-eyes>
            <svg class="crown" viewBox="0 0 24 24"><use href="#i-crown"/></svg>
            <img class="pet-cut pet-cat" src="{{ asset('images/hero/cat.png') }}" alt="A cute orange kitten looking up">
            <span class="eye" data-ex="9.5" data-ey="23.5" style="left:9.5%;top:23.5%;--lid:linear-gradient(#dadcd4 55%,#c2c4bb)"><img class="eyeball" src="{{ asset('images/hero/cat.png') }}" alt="" aria-hidden="true"><span class="lid"></span></span>
            <span class="eye" data-ex="34" data-ey="26" style="left:34%;top:26%;--lid:linear-gradient(#cdb899 55%,#b09a7c)"><img class="eyeball" src="{{ asset('images/hero/cat.png') }}" alt="" aria-hidden="true"><span class="lid"></span></span>
          </span>
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

    @once
    <script>
      (function () {
        var wraps = Array.prototype.slice.call(document.querySelectorAll('[data-eyes]'));
        var groups = [];
        wraps.forEach(function (wrap) {
          var eyes = Array.prototype.slice.call(wrap.querySelectorAll('.eye'));
          if (eyes.length) groups.push({ wrap: wrap, base: wrap.querySelector('img'), eyes: eyes });
        });
        if (!groups.length) return;

        var allEyes = groups.reduce(function (a, g) { return a.concat(g.eyes); }, []);

        // Align each socket's photo copy so the iris sits dead-centre at rest.
        function layout() {
          groups.forEach(function (g) {
            var W = g.wrap.clientWidth, H = g.wrap.clientHeight;
            g.eyes.forEach(function (eye) {
              var ball = eye.querySelector('.eyeball');
              ball.style.width = W + 'px';
              var ex = parseFloat(eye.dataset.ex) / 100 * W;
              var ey = parseFloat(eye.dataset.ey) / 100 * H;
              eye._bx = eye.clientWidth / 2 - ex;
              eye._by = eye.clientHeight / 2 - ey;
              eye._max = eye.clientWidth * 0.16; // how far the iris can glance
              ball.style.transform = 'translate(' + eye._bx + 'px,' + eye._by + 'px)';
            });
          });
        }

        var fine = window.matchMedia('(pointer:fine)').matches;
        var still = window.matchMedia('(prefers-reduced-motion:reduce)').matches;

        groups.forEach(function (g) {
          if (g.base.complete) layout();
          else g.base.addEventListener('load', layout);
        });
        layout();
        window.addEventListener('resize', layout, { passive: true });

        // Occasional blink, each animal on its own random timer so they blink apart.
        if (!still) {
          groups.forEach(function (g) {
            var lids = g.eyes.map(function (e) { return e.querySelector('.lid'); });
            (function blink() {
              setTimeout(function () {
                lids.forEach(function (lid) {
                  lid.animate(
                    [{ transform: 'translateY(-102%)' }, { transform: 'translateY(2%)', offset: 0.5 }, { transform: 'translateY(-102%)' }],
                    { duration: 210, easing: 'ease-in-out' }
                  );
                });
                blink();
              }, 2600 + Math.random() * 4600);
            })();
          });
        }

        if (!fine || still) return; // static, forward-looking eyes on touch / reduced-motion

        var headWraps = groups.filter(function (g) { return g.wrap.hasAttribute('data-head'); });
        var pending = false, mx = 0, my = 0;
        function clamp(v) { return v < -1 ? -1 : v > 1 ? 1 : v; }
        function track() {
          pending = false;
          allEyes.forEach(function (eye) {
            var r = eye.getBoundingClientRect();
            var dx = mx - (r.left + r.width / 2), dy = my - (r.top + r.height / 2);
            var d = Math.hypot(dx, dy) || 1;
            var m = Math.min(eye._max, d);
            eye.querySelector('.eyeball').style.transform =
              'translate(' + (eye._bx + dx / d * m).toFixed(1) + 'px,' + (eye._by + dy / d * m).toFixed(1) + 'px)';
          });
          // Fake a head turn: tilt the whole cut-out in 3D toward the cursor.
          headWraps.forEach(function (g) {
            var r = g.wrap.getBoundingClientRect();
            var nx = clamp((mx - (r.left + r.width / 2)) / (window.innerWidth * 0.45));
            var ny = clamp((my - (r.top + r.height * 0.4)) / (window.innerHeight * 0.45));
            var ry = (nx * 10).toFixed(2), rx = (-ny * 6).toFixed(2), rz = (nx * 2.5).toFixed(2);
            g.wrap.style.transform = 'perspective(1000px) rotateX(' + rx + 'deg) rotateY(' + ry + 'deg) rotate(' + rz + 'deg)';
          });
        }
        window.addEventListener('mousemove', function (e) {
          mx = e.clientX; my = e.clientY;
          if (!pending) { pending = true; requestAnimationFrame(track); }
        }, { passive: true });
      })();
    </script>
    @endonce

    {{-- stat bar --}}
    <div class="stats reveal">
      <div class="stat"><span class="ic" style="background:var(--p-pink)"><svg style="width:22px;color:var(--coral)" viewBox="0 0 24 24"><use href="#i-paw"/></svg></span><div><b>12,000+</b><small>Pets Available</small></div></div>
      <div class="stat"><span class="ic" style="background:var(--p-green)"><svg style="width:22px;color:var(--green)" viewBox="0 0 24 24"><use href="#i-heart"/></svg></span><div><b>20,000+</b><small>Successful Adoptions</small></div></div>
      <div class="stat"><span class="ic" style="background:var(--p-yellow)"><svg style="width:22px;color:var(--amber-deep)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11 12 4l9 7v8a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1Z"/></svg></span><div><b>980+</b><small>Shelters &amp; Rescues</small></div></div>
      <div class="stat"><span class="ic" style="background:var(--p-blue)"><svg style="width:22px;color:var(--green)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z"/></svg></span><div><b>100%</b><small>Verified &amp; Safe</small></div></div>
    </div>
  </div>
</section>
