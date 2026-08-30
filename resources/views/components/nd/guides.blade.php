@php
    $guides = [
        ['t' => 'New Pet Parent Guide', 'd' => 'Everything you need to give your pet.', 'bg' => 'var(--p-green)',  'fg' => 'var(--green)',      'icon' => 'paw'],
        ['t' => 'Prepare Your Home',    'd' => 'Make your home safe and pet-friendly.', 'bg' => 'var(--p-blue)',   'fg' => 'var(--green)',      'icon' => 'home'],
        ['t' => 'Training & Behaviour', 'd' => 'Tips for a happy, well-behaved pet.',   'bg' => 'var(--p-pink)',   'fg' => 'var(--coral)',      'icon' => 'heart'],
        ['t' => 'Health & Nutrition',   'd' => 'Feeding tips for a long, healthy life.','bg' => 'var(--p-yellow)', 'fg' => 'var(--amber-deep)', 'icon' => 'clock'],
    ];
@endphp

<section class="section" id="guides" aria-labelledby="guides-h">
  <div class="wrap">
    <div class="head reveal">
      <h2 id="guides-h">Guides &amp; Resources
        <svg class="heart" style="color:var(--coral)" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-spark"/></svg>
      </h2>
      <a href="{{ route('pets.index') }}" class="btn btn-ghost">Explore all guides
        <svg aria-hidden="true"><use href="#i-arrow"/></svg>
      </a>
    </div>
    <div class="guides">
      @foreach ($guides as $i => $g)
        {{-- Anchor, not a div: the whole card is the target, so it must be focusable. --}}
        <a class="guide reveal" href="{{ route('pets.index') }}"
           style="background:{{ $g['bg'] }}@if($i);transition-delay:{{ $i * .08 }}s @endif">
          <div>
            <span class="ic">
              @if ($g['icon'] === 'paw')
                <svg style="width:26px;color:{{ $g['fg'] }}" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-paw"/></svg>
              @elseif ($g['icon'] === 'heart')
                <svg style="width:26px;color:{{ $g['fg'] }}" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-heart"/></svg>
              @elseif ($g['icon'] === 'home')
                <svg style="width:26px;color:{{ $g['fg'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 11 12 4l9 7v8a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1Z"/></svg>
              @else
                <svg style="width:26px;color:{{ $g['fg'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2" stroke-linecap="round"/></svg>
              @endif
            </span>
            <h3>{{ $g['t'] }}</h3>
            <p>{{ $g['d'] }}</p>
          </div>
          <span class="go" aria-hidden="true">
            <svg style="width:16px" viewBox="0 0 24 24"><use href="#i-arrow"/></svg>
          </span>
        </a>
      @endforeach
    </div>
  </div>
</section>
