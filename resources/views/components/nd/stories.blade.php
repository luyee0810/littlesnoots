<section class="section" id="stories" aria-labelledby="stories-h">
  <div class="wrap">
    <div class="stories reveal">
      <svg class="doodle hide-sm" style="top:10%;right:26%;width:22px;color:var(--coral)"
           viewBox="0 0 24 24" aria-hidden="true"><use href="#i-heart-o"/></svg>
      <div class="stories-grid">
        <div>
          <h2 id="stories-h">Adoption Stories,<br>Forever Homes</h2>
          <p>Every adoption is a new beginning.</p>
          <a href="{{ route('pets.index') }}" class="btn btn-amber">Read more stories
            <svg aria-hidden="true"><use href="#i-arrow"/></svg>
          </a>
        </div>
        <div class="story-photos">
          <span class="washi" aria-hidden="true"></span>
          <img src="{{ asset('images/seed/dogs/dog-04.jpg') }}" alt="Bruno, adopted in March, asleep on a sofa" loading="lazy">
          <img src="{{ asset('images/seed/cats/cat-04.jpg') }}" alt="Oyen playing in her new home" loading="lazy">
          <img src="{{ asset('images/seed/cats/cat-16.jpg') }}" alt="Tompok resting in a sunny window" loading="lazy">
        </div>
        <figure class="quote-card">
          <div class="mark" aria-hidden="true">&ldquo;</div>
          <blockquote><p>Adopting Tompok was the best decision we ever made. He didn't just change our life, we changed his.</p></blockquote>
          <figcaption class="who">— Aisyah &amp; Tompok, Petaling Jaya</figcaption>
        </figure>
      </div>
    </div>
  </div>
</section>
