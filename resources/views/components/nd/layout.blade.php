{{-- New scrapbook design (studied from FurryFinder reference).
     Fully self-contained + scoped — does NOT load app.css, so it can't collide
     with the live "Field Notes" system. Ships on its own /newdesign route until
     the design is locked, then swaps into layouts/app.blade.php. --}}
<!doctype html>
<html lang="en" class="nd-root">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#f9f5ec">
<title>{{ $title ?? 'Little Snoots — Find your companion' }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;0,9..144,900;1,9..144,500&family=Instrument+Sans:ital,wght@0,400;0,500;0,600;1,400&family=Caveat:wght@600;700&display=swap" rel="stylesheet">
<style>
/* Hallmark · genre: playful · macrostructure: Marquee Hero · nav: N6 masthead · footer: Ft3
 * theme: studied-DNA (FurryFinder reference, user's own brand) · display: Fraunces · body: Instrument Sans */

.nd-root{
  --paper:        oklch(97% 0.014 92);
  --paper-2:      oklch(94.5% 0.02 90);
  --paper-card:   oklch(99% 0.006 95);
  --ink:          oklch(26% 0.03 158);
  --ink-soft:     oklch(42% 0.03 158);
  --ink-mute:     oklch(55% 0.02 150);

  --green:        oklch(38% 0.055 158);
  --green-deep:   oklch(30% 0.05 158);
  --green-ink:    oklch(24% 0.045 158);
  --amber:        oklch(84% 0.13 88);
  --amber-deep:   oklch(78% 0.14 82);
  --coral:        oklch(72% 0.15 32);
  --coral-deep:   oklch(66% 0.16 30);
  --tomato:       oklch(64% 0.19 33);
  --tomato-deep:  oklch(58% 0.2 32);

  --p-blue:       oklch(90% 0.045 235);
  --p-pink:       oklch(90% 0.05 10);
  --p-green:      oklch(91% 0.05 150);
  --p-yellow:     oklch(94% 0.07 95);
  --p-lilac:      oklch(90% 0.05 300);

  --display: "Fraunces", "Times New Roman", serif;
  --body:    "Instrument Sans", system-ui, -apple-system, sans-serif;
  --script:  "Caveat", "Segoe Script", cursive;

  --radius:  22px;
  --radius-s:14px;
  --shadow:  0 20px 45px -28px oklch(30% 0.05 158 / .45);
  --shadow-sm: 0 10px 26px -18px oklch(30% 0.05 158 / .4);

  --ease-out: cubic-bezier(.22,.61,.36,1);
  --ease-in-out: cubic-bezier(.65,.05,.36,1);
}

.nd-root *{box-sizing:border-box}
html.nd-root,.nd-root body{overflow-x:clip}
html.nd-root{scroll-behavior:smooth}
.nd-root body{
  margin:0;
  font-family:var(--body);
  background:var(--paper);
  color:var(--ink);
  font-size:16px;
  line-height:1.6;
  -webkit-font-smoothing:antialiased;
}
.nd img{max-width:100%;display:block}
.nd a{color:inherit;text-decoration:none}
.nd h1,.nd h2,.nd h3,.nd h4{margin:0;font-family:var(--display);font-weight:600;line-height:1.02;letter-spacing:-.02em;overflow-wrap:anywhere;min-width:0}
.nd p{margin:0}

.nd .wrap{max-width:1180px;margin-inline:auto;padding-inline:clamp(1.1rem,4vw,2.5rem)}
.nd .section{position:relative;padding-block:clamp(3.5rem,8vw,6rem)}

/* buttons */
.nd .btn{
  display:inline-flex;align-items:center;gap:.55rem;
  font-family:var(--body);font-weight:600;font-size:.98rem;
  padding:.85rem 1.5rem;border-radius:999px;border:2px solid transparent;
  cursor:pointer;transition:transform .18s var(--ease-out),box-shadow .25s var(--ease-out),background .2s;
  white-space:nowrap;
}
.nd .btn svg{width:1.05em;height:1.05em}
.nd .btn-green{background:var(--green);color:var(--paper);box-shadow:0 10px 22px -12px oklch(30% 0.05 158 / .7)}
.nd .btn-green:hover{transform:translateY(-2px);box-shadow:0 16px 30px -12px oklch(30% 0.05 158 / .8)}
.nd .btn-ghost{background:var(--paper-card);color:var(--ink);border-color:oklch(80% 0.02 150)}
.nd .btn-ghost:hover{transform:translateY(-2px);border-color:var(--green)}
.nd .btn-amber{background:var(--amber);color:var(--green-ink);box-shadow:0 10px 22px -14px var(--amber-deep)}
.nd .btn-amber:hover{transform:translateY(-2px);box-shadow:0 16px 28px -14px var(--amber-deep)}
.nd .btn-coral{background:var(--coral);color:#fff;box-shadow:0 10px 22px -14px var(--coral-deep)}
.nd .btn-coral:hover{transform:translateY(-2px)}
.nd .btn-tomato{background:var(--tomato);color:#fff;box-shadow:0 10px 22px -14px var(--tomato-deep);font-weight:700}
.nd .btn-tomato:hover{transform:translateY(-2px);box-shadow:0 16px 28px -14px var(--tomato-deep)}
.nd .btn:focus-visible{outline:3px solid var(--coral);outline-offset:3px}

.nd .eyebrow{font-family:var(--script);font-size:1.6rem;color:var(--coral);font-weight:700;line-height:1}
.nd .script{font-family:var(--script);color:var(--coral);font-weight:700}

/* doodles */
.nd .doodle{position:absolute;pointer-events:none;z-index:1;color:var(--coral)}
.nd .doodle.blue{color:var(--p-blue)}
.nd .doodle.amber{color:var(--amber-deep)}
.nd .doodle.green{color:var(--green)}
@keyframes nd-floaty{0%,100%{transform:translateY(0) rotate(var(--r,0deg))}50%{transform:translateY(-9px) rotate(var(--r,0deg))}}
.nd .float{animation:nd-floaty 5s var(--ease-in-out) infinite}

/* nav (N6 masthead) */
.nd .nav{position:sticky;top:0;z-index:50;background:oklch(97% 0.014 92 / .82);backdrop-filter:blur(10px);border-bottom:1px solid oklch(88% 0.02 120 / .7)}
.nd .nav-in{display:flex;align-items:center;gap:1.5rem;height:74px}
.nd .brand{display:flex;align-items:center;gap:.5rem;font-family:var(--display);font-weight:700;font-size:1.35rem;letter-spacing:-.02em}
.nd .brand .paw{width:26px;height:26px;color:var(--green)}
.nd .nav-links{display:flex;gap:1.9rem;margin-inline:auto;font-weight:700;font-size:1rem}
.nd .nav-links a{position:relative;padding:.3rem 0;color:var(--ink)}
.nd .nav-links a:hover{color:var(--green)}
.nd .nav-links a.active{color:var(--ink)}
.nd .nav-links a.active::after{content:"";position:absolute;left:0;right:0;bottom:-4px;height:2px;background:var(--ink);border-radius:2px}
.nd .nav-right{display:flex;align-items:center;gap:.9rem}
.nd .icon-btn{width:40px;height:40px;display:grid;place-items:center;border-radius:50%;border:1px solid oklch(85% 0.02 140);background:var(--paper-card);color:var(--ink-soft);cursor:pointer;transition:.2s var(--ease-out)}
.nd .icon-btn:hover{color:var(--green);border-color:var(--green);transform:translateY(-1px)}
.nd .icon-btn svg{width:18px;height:18px}
.nd .nav-toggle{display:none}

/* hero */
.nd .hero{position:relative;overflow:hidden;padding-top:clamp(2.5rem,5vw,4rem)}
.nd .hero-grid{display:grid;grid-template-columns:1.02fr 1fr;gap:clamp(2rem,4vw,3.5rem);align-items:center}
.nd .hero-badge{display:inline-flex;align-items:center;gap:.5rem;background:var(--p-yellow);color:var(--green-ink);font-family:var(--display);font-style:italic;font-weight:500;font-size:.98rem;padding:.4rem 1.1rem;border-radius:999px}
.nd .hero h1{font-size:clamp(2.9rem,7.5vw,5rem);font-weight:700;margin-top:1.4rem}
.nd .hero h1 .hi-em{font-family:var(--display);font-style:italic;font-weight:500;color:var(--coral);display:inline-block;position:relative}
.nd .hero h1 .hi-em .u{position:absolute;left:-1%;width:102%;height:.34em}
.nd .hero h1 .hi-em .u1{color:var(--amber-deep);bottom:-.26em}
.nd .hero h1 .hi-em .u2{color:var(--coral);bottom:-.16em}
.nd .hero-sub{margin-top:1.5rem;max-width:34ch;color:var(--ink-soft);font-size:1.08rem}
.nd .hero-cta{display:flex;flex-wrap:wrap;gap:.8rem;margin-top:1.9rem}
.nd .adopters{display:flex;align-items:center;gap:.9rem;margin-top:1.9rem}
.nd .avatars{display:flex}
.nd .avatars img{width:44px;height:44px;border-radius:50%;border:3px solid var(--paper);object-fit:cover;margin-left:-14px;background:var(--p-green)}
.nd .avatars img:first-child{margin-left:0}
.nd .adopters span{font-weight:600;font-size:.95rem}
.nd .adopters b{color:var(--green)}

/* hero art */
.nd .hero-art{position:relative;min-height:480px}
.nd .blob{position:absolute;left:2%;top:4%;width:74%;height:88%;background:radial-gradient(120% 120% at 45% 35%,var(--amber) 0%,var(--amber-deep) 68%,var(--coral) 135%);border-radius:52% 48% 56% 44%/54% 44% 56% 46%;filter:saturate(1.05);z-index:0}
.nd .blob-2{position:absolute;right:-8%;bottom:-6%;width:44%;height:44%;background:var(--p-pink);border-radius:50% 50% 46% 54%/52% 48% 52% 48%;opacity:.85;z-index:0}
.nd .pets{position:absolute;inset:0;display:flex;align-items:flex-end;justify-content:flex-start;padding-left:6%;gap:0;z-index:2}
.nd .pet-cut{object-fit:cover;filter:drop-shadow(0 18px 28px oklch(30% 0.05 158 / .35))}
.nd .pet-dog{width:56%;max-width:280px;aspect-ratio:4/5;border-radius:22px;border:5px solid var(--paper);position:relative;z-index:2}
/* Transparent cut-out: drop the photo-card frame so the silhouette shows through */
.nd .pet-cut.is-cutout{object-fit:contain;object-position:bottom;aspect-ratio:720/988;border:0;border-radius:0;width:100%;filter:drop-shadow(0 16px 22px oklch(30% 0.05 158 / .32))}
/* Realistic cursor-tracking eyes: a clipped socket over each real eye holds an
   aligned copy of the photo; shifting it moves the iris + catchlight (JS below). */
.nd .eye-cut{position:relative;display:inline-block;align-self:flex-end;line-height:0}
/* Head-turn: the cut-out tilts in 3D toward the cursor, pivoting at the neck (JS). */
.nd .eye-cut[data-head]{transform-origin:50% 80%;transition:transform .18s ease-out;backface-visibility:hidden}
.nd .dog-cut{width:56%;max-width:300px;z-index:2}
.nd .cat-cut{width:32%;max-width:158px;margin-left:-9%;z-index:3}
.nd .cat-cut .pet-cat{width:100%;height:auto;aspect-ratio:auto;border:0;border-radius:0;object-fit:contain;filter:drop-shadow(0 14px 20px oklch(30% 0.05 158 / .3))}
.nd .eye-cut .eye{position:absolute;z-index:3;aspect-ratio:1;border-radius:50%;overflow:hidden;transform:translate(-50%,-50%);pointer-events:none;box-shadow:inset 0 0 3px 1px rgba(0,0,0,.4)}
.nd .dog-cut .eye{width:6.6%}
.nd .cat-cut .eye{width:11.8%;box-shadow:none}
.nd .dog-cut .eye:nth-of-type(1){left:34%;top:23%}
.nd .dog-cut .eye:nth-of-type(2){left:67%;top:24%}
.nd .eye-cut .eyeball{position:absolute;left:0;top:0;max-width:none;transition:transform .1s ease-out;will-change:transform}
.nd .eye-cut .lid{position:absolute;inset:-2px;background:var(--lid,linear-gradient(#131313 60%,#241a13));transform:translateY(-102%);will-change:transform}
@media (prefers-reduced-motion:reduce){.nd .eye-cut .eyeball{transition:none}.nd .eye-cut .lid{display:none}}
.nd .cat-wrap{position:relative;z-index:3;margin-left:-14%;align-self:flex-end}
.nd .pet-cat{width:150px;max-width:42vw;aspect-ratio:4/5;border-radius:20px;border:5px solid var(--paper)}
.nd .crown{position:absolute;top:-20px;left:50%;transform:translateX(-50%) rotate(-6deg);width:34px;color:var(--amber-deep);z-index:4}

.nd .card-stack{position:absolute;top:8%;right:-3%;width:min(58%,232px);display:flex;flex-direction:column;gap:.85rem;z-index:5}
.nd .float-card{position:relative;background:var(--paper-card);border-radius:16px;padding:.7rem .8rem;box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:.65rem;border:1px solid oklch(93% 0.01 120)}
.nd .float-card::before{content:"";position:absolute;top:-8px;right:24px;width:34px;height:15px;background:var(--p-pink);opacity:.85;border-radius:3px;transform:rotate(-8deg)}
.nd .float-card:last-child::before{background:var(--amber);opacity:.9;transform:rotate(7deg)}
.nd .seal{position:relative;width:42px;height:42px;flex:none}
.nd .seal-bg{width:100%;height:100%}
.nd .seal-ic{position:absolute;inset:0;margin:auto;width:20px;height:20px}
.nd .float-card small{display:block;font-size:.68rem;color:var(--ink-mute);line-height:1.2}
.nd .float-card strong{font-size:.82rem;display:block;line-height:1.15}
.nd .speech{position:absolute;top:0;left:4%;background:var(--paper-card);border-radius:16px 16px 16px 4px;padding:.5rem .95rem;font-family:var(--display);font-style:italic;font-weight:500;color:var(--ink-mute);box-shadow:var(--shadow-sm);z-index:6;font-size:1rem}

/* stat bar */
.nd .stats{background:var(--paper-card);border-radius:var(--radius);box-shadow:var(--shadow);display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;padding:1.6rem 2rem;margin-top:1rem;position:relative;z-index:3}
.nd .stat{display:flex;align-items:center;gap:.85rem;justify-content:center}
.nd .stat .ic{width:48px;height:48px;border-radius:50%;display:grid;place-items:center;flex:none}
.nd .stat b{font-family:var(--display);font-size:1.5rem;font-weight:600;display:block;line-height:1}
.nd .stat small{color:var(--ink-mute);font-size:.85rem}

/* ---------- section head ---------- */
.nd .head{display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:2.4rem}
.nd .head h2{font-size:clamp(1.9rem,4.5vw,2.9rem);display:flex;align-items:center;gap:.6rem;flex-wrap:wrap}
.nd .head .heart{width:1.4rem;height:1.4rem;color:var(--green)}
.nd .center{text-align:center}
.nd .center h2{justify-content:center}

/* screen-reader-only utility (scoped) */
.nd .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}

/* ---------- pet cards ---------- */
.nd .pet-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1.4rem}
.nd .pcard{background:var(--paper-card);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);border:1px solid oklch(93% 0.01 120);transition:transform .25s var(--ease-out),box-shadow .25s var(--ease-out)}
.nd .pcard:hover{transform:translateY(-6px);box-shadow:var(--shadow)}
.nd .pcard .photo{aspect-ratio:4/3.4;position:relative;overflow:hidden;margin:12px 12px 0;border-radius:var(--radius-s)}
.nd .pcard .photo img{width:100%;height:100%;object-fit:cover;transition:transform .5s var(--ease-out)}
.nd .pcard:hover .photo img{transform:scale(1.04)}
.nd .pcard .fav{position:absolute;top:10px;right:10px;width:32px;height:32px;border-radius:50%;background:oklch(99% 0.006 95 / .92);display:grid;place-items:center;color:var(--coral)}
.nd .pcard .photo-fallback{width:100%;height:100%;display:grid;place-items:center;background:var(--p-green)}
.nd .pcard .photo-fallback svg{width:38px;height:38px;color:var(--green);opacity:.55}
.nd .pcard .body{padding:1rem 1.1rem 1.2rem}
.nd .pname{display:flex;align-items:center;justify-content:space-between}
.nd .pname h3{font-family:var(--display);font-size:1.3rem;font-weight:600}
.nd .pname .g{color:var(--ink-mute)}
.nd .pmeta{color:var(--ink-mute);font-size:.86rem;margin-top:.35rem;display:flex;gap:.4rem;flex-wrap:wrap;align-items:center}
.nd .pmeta .dot{width:4px;height:4px;border-radius:50%;background:var(--ink-mute);opacity:.5}
.nd .tags{display:flex;gap:.4rem;margin:.8rem 0 1rem;flex-wrap:wrap}
.nd .tag{font-size:.75rem;font-weight:500;padding:.28rem .7rem;border-radius:999px}
.nd .pcard .btn{width:100%;justify-content:center;font-size:.9rem;padding:.7rem}

/* ---------- process ---------- */
.nd .process{position:relative}
.nd .steps{display:grid;grid-template-columns:repeat(4,1fr);gap:1.4rem;position:relative;z-index:2;list-style:none;margin:0;padding:0}
.nd .step{text-align:center}
.nd .step .ring{width:72px;height:72px;border-radius:50%;display:grid;place-items:center;margin:0 auto 1.1rem;position:relative}
.nd .step .ring svg{width:30px;height:30px}
.nd .step h3{font-family:var(--display);font-size:1.15rem;margin-bottom:.5rem}
.nd .step p{color:var(--ink-mute);font-size:.9rem;max-width:24ch;margin-inline:auto}
.nd .dash-line{position:absolute;top:36px;left:12%;right:12%;height:2px;z-index:1;color:oklch(78% 0.08 30)}

/* ---------- stories band ---------- */
.nd .stories{background:var(--paper-2);border-radius:var(--radius);padding:clamp(1.6rem,4vw,2.6rem);position:relative;overflow:hidden}
.nd .stories-grid{display:grid;grid-template-columns:.85fr 1.2fr 1fr;gap:1.6rem;align-items:center}
.nd .stories h2{font-size:clamp(1.6rem,3.5vw,2.2rem)}
.nd .stories p{color:var(--ink-soft);margin:.8rem 0 1.3rem;font-size:.96rem}
.nd .story-photos{display:flex;gap:.6rem;position:relative}
.nd .story-photos img{width:33%;aspect-ratio:1;object-fit:cover;border-radius:14px;border:4px solid var(--paper-card);box-shadow:var(--shadow-sm)}
.nd .story-photos img:nth-child(2){transform:rotate(-4deg)}
.nd .story-photos img:nth-child(4){transform:rotate(4deg)}
.nd .washi{position:absolute;top:-14px;left:50%;transform:translateX(-50%) rotate(-3deg);width:70px;height:26px;background:var(--amber);opacity:.8;border-radius:3px;z-index:3}
.nd .quote-card{background:var(--paper-card);border-radius:18px;padding:1.4rem;box-shadow:var(--shadow-sm);position:relative;margin:0}
.nd .quote-card .mark{font-family:var(--display);font-size:3rem;color:var(--coral);line-height:.6;font-weight:900}
.nd .quote-card blockquote{margin:0}
.nd .quote-card p{font-family:var(--display);font-size:1.05rem;color:var(--ink);font-weight:500;font-style:italic}
.nd .quote-card .who{margin-top:1rem;font-family:var(--script);color:var(--green);font-size:1.2rem;font-weight:700}

/* ---------- guides ---------- */
.nd .guides{display:grid;grid-template-columns:repeat(4,1fr);gap:1.2rem}
.nd .guide{border-radius:var(--radius);padding:1.3rem;min-height:170px;display:flex;flex-direction:column;justify-content:space-between;transition:transform .25s var(--ease-out);color:var(--ink)}
.nd .guide:hover{transform:translateY(-5px)}
.nd .guide:focus-visible{outline:3px solid var(--coral);outline-offset:3px}
.nd .guide .ic{width:52px;height:52px;border-radius:14px;background:oklch(100% 0 0 / .5);display:grid;place-items:center;margin-bottom:1rem}
.nd .guide h3{font-family:var(--display);font-size:1.1rem}
.nd .guide p{font-size:.85rem;color:var(--ink-soft);margin-top:.4rem}
.nd .guide .go{align-self:flex-end;width:36px;height:36px;border-radius:50%;background:var(--paper-card);display:grid;place-items:center;color:var(--green);margin-top:1rem}

/* ---------- testimonial band (dark green) ---------- */
.nd .loved{background:var(--green-ink);color:var(--paper);border-radius:var(--radius);padding:clamp(2rem,5vw,3.2rem);position:relative;overflow:hidden}
.nd .loved-grid{display:grid;grid-template-columns:1fr 2fr;gap:2rem;align-items:center;position:relative;z-index:2}
.nd .loved h2{color:var(--paper);font-size:clamp(1.8rem,4vw,2.6rem)}
.nd .loved .intro p{color:oklch(90% 0.02 120);margin-top:1rem;font-size:.98rem;max-width:30ch}
.nd .reviews{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem}
.nd .review{background:oklch(99% 0.006 95 / .07);border:1px solid oklch(99% 0.006 95 / .14);border-radius:16px;padding:1.1rem;margin:0}
.nd .review .stars{color:var(--amber);letter-spacing:2px;font-size:.9rem;margin-bottom:.6rem}
.nd .review blockquote{margin:0}
.nd .review p{font-size:.9rem;color:oklch(95% 0.01 100)}
.nd .review .who{margin-top:.9rem;font-family:var(--script);font-size:1.1rem;color:var(--amber);font-weight:700}

/* ---------- footer (Ft3) ---------- */
.nd .foot{border-top:1px solid oklch(88% 0.02 120);padding-block:clamp(2.5rem,5vw,3.5rem) 2rem;margin-top:clamp(3rem,6vw,5rem)}
.nd .foot-grid{display:grid;grid-template-columns:1.6fr repeat(4,1fr) 1.4fr;gap:2rem}
.nd .foot .brand{margin-bottom:1rem}
.nd .foot .tagline{color:var(--ink-mute);font-size:.9rem;max-width:26ch}
.nd .socials{display:flex;gap:.6rem;margin-top:1.2rem}
.nd .socials a{width:38px;height:38px;border-radius:50%;background:var(--green);color:var(--paper);display:grid;place-items:center;transition:.2s var(--ease-out)}
.nd .socials a:hover{transform:translateY(-2px);background:var(--green-deep)}
.nd .socials svg{width:17px;height:17px}
.nd .fcol h4{font-family:var(--body);font-weight:600;font-size:.95rem;margin-bottom:.9rem}
.nd .fcol a{display:block;color:var(--ink-mute);font-size:.9rem;padding:.28rem 0}
.nd .fcol a:hover{color:var(--green)}
.nd .newsletter h4{font-family:var(--body);font-weight:600;margin-bottom:.4rem}
.nd .newsletter p{font-size:.85rem;color:var(--ink-mute);margin-bottom:.9rem}
.nd .news-label{display:block;font-size:.8rem;font-weight:600;color:var(--ink-soft);margin-bottom:.4rem}
.nd .news-form{display:flex;background:var(--green-ink);border-radius:999px;padding:.3rem;align-items:center}
.nd .news-form input{flex:1;background:none;border:none;color:var(--paper);padding:.5rem .9rem;font-family:var(--body);font-size:.9rem;min-width:0}
.nd .news-form input::placeholder{color:oklch(85% 0.02 120 / .7)}
.nd .news-form input:focus{outline:none}
.nd .news-form:focus-within{outline:3px solid var(--coral);outline-offset:2px}
.nd .news-form button{width:40px;height:40px;border-radius:50%;background:var(--amber);border:none;color:var(--green-ink);cursor:pointer;display:grid;place-items:center;flex:none;transition:.2s}
.nd .news-form button:hover{transform:translateX(2px)}
.nd .copyright{margin-top:2.5rem;padding-top:1.5rem;border-top:1px solid oklch(88% 0.02 120);color:var(--ink-mute);font-size:.85rem;display:flex;justify-content:space-between;flex-wrap:wrap;gap:.5rem}

/* reveal */
.nd .reveal{opacity:0;transform:translateY(22px);transition:opacity .6s var(--ease-out),transform .6s var(--ease-out)}
.nd .reveal.in{opacity:1;transform:none}

/* responsive */
@media(max-width:960px){
  .nd .nav-links{display:none}
  .nd .nav-toggle{display:grid}
  .nd .hero-grid{grid-template-columns:1fr;gap:2.5rem}
  .nd .hero-art{min-height:380px;max-width:460px;margin-inline:auto;width:100%}
  .nd .stats{grid-template-columns:repeat(2,1fr);gap:1.4rem}
  .nd .pet-cards{grid-template-columns:repeat(2,minmax(0,1fr))}
  .nd .steps{grid-template-columns:repeat(2,1fr);gap:2rem}
  .nd .dash-line{display:none}
  .nd .stories-grid{grid-template-columns:1fr;gap:1.4rem}
  .nd .guides{grid-template-columns:repeat(2,1fr)}
  .nd .loved-grid{grid-template-columns:1fr}
  .nd .reviews{grid-template-columns:1fr}
  .nd .foot-grid{grid-template-columns:1fr 1fr}
  .nd .foot .brandcol,.nd .newsletter{grid-column:1/-1}
  .nd .doodle.hide-sm{display:none}
}
@media(max-width:540px){
  .nd .stats{grid-template-columns:1fr}
  .nd .pet-cards{grid-template-columns:1fr}
  .nd .steps{grid-template-columns:1fr}
  .nd .guides{grid-template-columns:1fr}
  .nd .foot-grid{grid-template-columns:1fr}
  .nd .head{flex-direction:column;align-items:flex-start}
}
@media(prefers-reduced-motion:reduce){
  .nd *{animation:none!important}
  .nd .reveal{opacity:1;transform:none;transition:none}
  html.nd-root{scroll-behavior:auto}
}
</style>
</head>
<body>
<div class="nd">

{{-- reusable doodle + icon symbols --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-paw" viewBox="0 0 24 24"><path fill="currentColor" d="M12 14.5c2.5 0 5 1.9 5 4.1 0 1.4-1.1 2.4-2.6 2.4-1 0-1.7-.5-2.4-.5s-1.4.5-2.4.5C8.1 21 7 20 7 18.6c0-2.2 2.5-4.1 5-4.1Zm-5.4-1.2c1 .3 1.5 1.6 1.1 2.9-.4 1.3-1.5 2.1-2.5 1.8-1-.3-1.5-1.6-1.1-2.9.4-1.3 1.5-2.1 2.5-1.8Zm10.8 0c1-.3 2.1.5 2.5 1.8.4 1.3-.1 2.6-1.1 2.9-1 .3-2.1-.5-2.5-1.8-.4-1.3.1-2.6 1.1-2.9ZM9.3 6.4c1.1 0 1.9 1.1 1.9 2.5S10.4 11.4 9.3 11.4 7.4 10.3 7.4 8.9 8.2 6.4 9.3 6.4Zm5.4 0c1.1 0 1.9 1.1 1.9 2.5s-.8 2.5-1.9 2.5-1.9-1.1-1.9-2.5.8-2.5 1.9-2.5Z"/></symbol>
  <symbol id="i-heart" viewBox="0 0 24 24"><path fill="currentColor" d="M12 21s-7.5-4.6-9.7-9C1 9.2 2.3 6 5.4 6c1.9 0 3.2 1.1 4 2.3.8-1.2 2.1-2.3 4-2.3 3.1 0 4.4 3.2 3.1 6-2.2 4.4-9.7 9-9.7 9Z"/></symbol>
  <symbol id="i-heart-o" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" d="M12 20s-6.7-4.1-8.7-8C2 9.4 3.1 6.8 5.7 6.8c1.7 0 2.9 1 3.6 2.1.7-1.1 1.9-2.1 3.6-2.1 2.6 0 3.7 2.6 2.4 5.1-2 3.9-8.7 8-8.7 8Z" transform="translate(-.4)"/></symbol>
  <symbol id="i-spark" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2c.6 4.8 2.2 6.4 7 7-4.8.6-6.4 2.2-7 7-.6-4.8-2.2-6.4-7-7 4.8-.6 6.4-2.2 7-7Z"/></symbol>
  <symbol id="i-star" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" d="m12 3 2.5 5.3 5.5.7-4 4 1 5.6-5-2.8-5 2.8 1-5.6-4-4 5.5-.7L12 3Z"/></symbol>
  <symbol id="i-arrow" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></symbol>
  <symbol id="i-crown" viewBox="0 0 24 24"><path fill="currentColor" d="M3 7.5l4.2 3.2L12 4l4.8 6.7L21 7.5 19 19H5L3 7.5Z"/></symbol>
  <symbol id="i-seal" viewBox="0 0 100 100"><g fill="currentColor"><circle cx="50" cy="50" r="35.5"/><circle cx="88" cy="50" r="9.6"/><circle cx="82.9" cy="69" r="9.6"/><circle cx="69" cy="82.9" r="9.6"/><circle cx="50" cy="88" r="9.6"/><circle cx="31" cy="82.9" r="9.6"/><circle cx="17.1" cy="69" r="9.6"/><circle cx="12" cy="50" r="9.6"/><circle cx="17.1" cy="31" r="9.6"/><circle cx="31" cy="17.1" r="9.6"/><circle cx="50" cy="12" r="9.6"/><circle cx="69" cy="17.1" r="9.6"/><circle cx="82.9" cy="31" r="9.6"/></g></symbol>
</svg>

{{ $slot }}

</div>
<script>
  (function(){
    var els = document.querySelectorAll('.nd .reveal');
    if(!('IntersectionObserver' in window)){els.forEach(function(e){e.classList.add('in')});return;}
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target);} });
    },{threshold:.12, rootMargin:'0px 0px -8% 0px'});
    els.forEach(function(e){io.observe(e)});
  })();
</script>
</body>
</html>
