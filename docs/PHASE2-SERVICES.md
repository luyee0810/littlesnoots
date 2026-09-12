# Phase 2 — Pet Services

Marketplace for pet services, modelled on [petbacker.com](https://www.petbacker.com): individual
providers (sitters, walkers, groomers) publish a profile with per-service pricing; pet owners
search by keywords, location and service and **book a specific provider directly**.

Demo data is Malaysia-based (Klang Valley and beyond) and priced in **MYR (RM)**.

**Decisions taken up front**

| Question | Decision |
| --- | --- |
| Booking flow | **Direct booking.** Owner picks a provider and requests a booking; provider accepts or declines. No quote broadcast / bidding. |
| Who providers are | **Individuals, self-serve.** Any registered user can open a provider profile from their dashboard. Businesses are not modelled in Phase 2. |
| Search | **Keywords + location + service category.** No date-availability, price or attribute facets in Phase 2. |
| Money | **Out of scope.** No payments, no commission, no payouts — owner and provider settle off-platform. The agreed price is recorded on the booking for reference only. |
| Scope | Build is sequenced **2a → 2b → 2c**, and 2a ships standalone. |

---

## 1. Service catalogue

Seven categories, matching PetBacker's core set. Each carries its own **pricing unit**, which
drives how the booking form and price breakdown render.

| Category | Slug | Pricing unit | Date input | Where it happens |
| --- | --- | --- | --- | --- |
| Pet Boarding | `boarding` | per night | range | Provider's home |
| House Sitting | `house-sitting` | per night | range | Owner's home |
| Dog Walking | `dog-walking` | per walk | single + repeat | Owner's neighbourhood |
| Pet Daycare | `daycare` | per day | range | Provider's home |
| Pet Grooming | `grooming` | per session | single datetime | Provider's or mobile |
| Pet Taxi | `pet-taxi` | per trip | single datetime | Pickup → dropoff |
| Pet Training | `training` | per session | single datetime | Either |

`pricing_unit` is an enum (`night`, `day`, `walk`, `session`, `trip`, `hour`) and
`requires_date_range` is a boolean — together they let one booking form serve all seven
categories rather than seven bespoke forms.

---

## 2. Schema

Follows the existing house pattern: a `*_categories` taxonomy plus a photos table, same as
`species`/`pet_photos`.

```
users
 └── provider_profiles (1:1)
       ├── provider_services      (one row per category the provider offers, with price)
       ├── provider_photos
       ├── provider_unavailable_dates
       ├── bookings ──────────────── users (the owner)
       │     └── reviews (1:1 with a completed booking)
       └── (2c) conversations → messages

service_categories ──< provider_services
                   ──< bookings   (denormalised, for reporting/filters)
```

### 2a tables

**`service_categories`**
`id, name, slug (unique), tagline, description, icon, pricing_unit, requires_date_range,
sort_order, is_active, timestamps`

**`provider_profiles`** — 1:1 with `users`, route-bound on `slug`
- Identity: `user_id` (unique, cascade), `slug` (unique), `headline`, `bio`
- Location: `address1`, `city` (index), `state`, `postcode` (index), `country`,
  `latitude decimal(10,7)`, `longitude decimal(10,7)`, `service_radius_km`
- Home & experience: `years_experience`, `home_type`, `has_fenced_yard`, `has_own_pets`,
  `is_smoke_free`, `has_insurance`
- Capacity: `accepts_species` (json → `species` slugs), `accepts_sizes` (json → reuses the
  `pets.size` vocabulary), `max_pets_per_booking`
- Booking behaviour: `available_days` (json weekly mask). Not a search facet — it's shown on the
  profile and enforced when a booking is submitted.
- Lifecycle: `status` enum(`draft`, `pending`, `approved`, `suspended`) default `draft`, indexed;
  `published_at`
- Trust (columns land now, populated in 2b): `verified_email_at`, `verified_phone_at`,
  `verified_id_at`, `background_check_at`
- Denormalised counters: `rating_avg decimal(3,2)`, `reviews_count`, `bookings_count`,
  `response_rate`, `response_time_minutes`
- `timestamps`, `softDeletes`

**`provider_services`** — what a provider offers, and for how much
`id, provider_profile_id, service_category_id, title (nullable override), description,
price decimal(8,2), price_unit, currency, additional_pet_price (nullable), min_units,
max_pets, is_active, timestamps`
Unique on `(provider_profile_id, service_category_id)`.

**`provider_photos`**
`id, provider_profile_id, url, caption, is_primary, sort_order, timestamps` — mirrors `pet_photos`.

**`provider_unavailable_dates`**
`id, provider_profile_id, date, reason` — unique on `(provider_profile_id, date)`.
Blocked-out days a provider sets on their own profile. Used to **validate** a submitted booking
and to grey out dates on the profile; it does not filter search results in Phase 2.

**`bookings`**
- Keys: `reference` (short public code, unique), `provider_profile_id`, `provider_service_id`
  (restrict on delete), `service_category_id`, `user_id` (the owner)
- When: `starts_at`, `ends_at` (nullable for single-shot services), `unit_quantity`
  (nights/walks/sessions), `unit_label`
- **Pet snapshot** (denormalised so history survives profile edits): `pet_name`,
  `pet_species_id` (nullable FK), `pet_breed`, `pet_size`, `pet_count`, `pet_notes`
- Owner snapshot: `owner_name`, `owner_email`, `owner_phone`, plus the service address when the
  service happens at the owner's home
- **Price snapshot**: `unit_price`, `additional_pet_price`, `total`, `currency` — the agreed
  figure, captured at booking time and never recomputed from the live `provider_services` row.
  Recorded for reference; the platform does not collect it.
- Flow: `status` enum(`pending`, `accepted`, `declined`, `in_progress`, `completed`,
  `cancelled_by_owner`, `cancelled_by_provider`, `expired`) default `pending`, indexed;
  `message`, `provider_response`, `responded_at`, `cancelled_at`, `completed_at`, `expires_at`
- Composite indexes: `(provider_profile_id, status)`, `(user_id, status)`, `(starts_at)`

### 2b/2c tables

**`reviews`** — `booking_id` (unique), `provider_profile_id`, `user_id`, `rating` (1–5),
sub-ratings `punctuality`/`communication`/`care`/`value`, `title`, `body`, `provider_reply`,
`replied_at`, `published_at`. Only creatable against a `completed` booking.

**`conversations` / `messages`** (2c) — scoped to a booking or a provider+owner pair.

### Notes on things that could go wrong

- **`pets` is not the owner's pet.** The existing `pets` table is a shelter's adoption listing.
  Owner-side pet profiles ("My Pets") are a separate concern — 2a keeps pet details as snapshot
  columns on `bookings`, and 2b adds an `owner_pets` table that pre-fills the booking form.
- **Don't extend `users.role`.** A user is an adopter *and* a provider; the single-value enum
  can't express that. Provider status = an `approved` `provider_profile` exists. Add
  `User::isProvider()` and `User::providerProfile()`. This leaves the existing sign-up
  account-type → role mapping untouched.
- **Geo search.** Location matching is `city`/`postcode` with `ilike`, matching the existing
  search convention. `latitude`/`longitude` and `service_radius_km` are stored now so
  radius/distance search can be added later without a migration — if that day comes, enable the
  Postgres `cube` + `earthdistance` extensions and add a GiST index before reaching for PostGIS.

---

## 3. Routes

```php
// Public discovery
Route::get('/services', [ServiceCategoryController::class, 'index'])->name('services.index');
Route::get('/services/{category:slug}', [ServiceCategoryController::class, 'show'])->name('services.show');
Route::get('/sitters/{provider}', [ProviderController::class, 'show'])->name('providers.show');

// Booking (auth)
Route::post('/sitters/{provider}/book', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

// Become a provider (auth)
Route::prefix('provider')->name('provider.')->middleware('auth')->group(function () {
    Route::get('/onboarding', [ProviderOnboardingController::class, 'create'])->name('onboarding');
    Route::post('/onboarding', [ProviderOnboardingController::class, 'store']);
    Route::get('/profile',  [ProviderProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',  [ProviderProfileController::class, 'update'])->name('profile.update');
    Route::resource('services', ProviderServiceController::class)->except(['show']);
    Route::get('/bookings', [ProviderBookingController::class, 'index'])->name('bookings.index');
    Route::patch('/bookings/{booking}/accept',   [ProviderBookingController::class, 'accept'])->name('bookings.accept');
    Route::patch('/bookings/{booking}/decline',  [ProviderBookingController::class, 'decline'])->name('bookings.decline');
    Route::patch('/bookings/{booking}/complete', [ProviderBookingController::class, 'complete'])->name('bookings.complete');
});
```

`ProviderProfile::getRouteKeyName()` returns `slug`, same as `Pet`.

## 4. Booking state machine

```
                 ┌──> declined
pending ─────────┼──> expired            (auto after expires_at, scheduled job)
   │             └──> cancelled_by_owner
   ↓ provider accepts
accepted ──> in_progress ──> completed ──> review window opens (2b)
   └──> cancelled_by_owner | cancelled_by_provider   (no fee — nothing is collected)
```

Transitions live in a `Booking` model method (`markAccepted()`, `markCompleted()`, …) guarded by
an `assertCan()` check, not scattered across controllers. `in_progress` and `completed` are set by
a scheduled job off `starts_at`/`ends_at`, with a manual override for the provider.

## 5. Controllers, requests, policies

| Layer | Classes |
| --- | --- |
| Controllers | `ServiceCategoryController`, `ProviderController`, `BookingController`, `ProviderOnboardingController`, `ProviderProfileController`, `ProviderServiceController`, `ProviderBookingController` |
| Form requests | `StoreProviderProfileRequest`, `UpdateProviderProfileRequest`, `StoreProviderServiceRequest`, `StoreBookingRequest`, `RespondToBookingRequest`, `StoreReviewRequest` (2b) |
| Policies | `ProviderProfilePolicy` (owner or admin edits), `BookingPolicy` (`view`: owner or provider; `respond`: provider; `cancel`: either, policy-dependent), `ReviewPolicy` (owner of a completed booking, once) |
| Scopes | `ProviderProfile::approved()`, `published()`, `inCategory($slug)`, `inLocation($term)`, `search($keywords)` — the `PetController` filter style, moved into scopes |

`StoreBookingRequest` is where the important validation sits: dates in the future,
`ends_at > starts_at`, pet count ≤ the service's `max_pets`, the category actually offered by
that provider, and no clash with `provider_unavailable_dates`. **Price is calculated
server-side** from the `provider_services` row — never accepted from the form.

## 6. Search

Three inputs, one search bar, present on both `/services` and `/services/{category}`:

| Input | Param | Behaviour |
| --- | --- | --- |
| Keywords | `q` | `ilike '%term%'` across `provider_profiles.headline`, `.bio`, and — via `whereHas` — `provider_services.title` / `.description` |
| Location | `location` | `ilike` against `city` **or** `postcode` |
| Service | `category` | exact match on `service_categories.slug`; pre-filled when the user came in from a category page |

```php
ProviderProfile::query()
    ->approved()->published()
    ->with(['user', 'photos', 'services.category'])
    ->when($request->filled('category'), fn ($q) => $q->inCategory($request->string('category')))
    ->when($request->filled('location'), fn ($q) => $q->inLocation($request->string('location')))
    ->when($request->filled('q'), fn ($q) => $q->search($request->string('q')))
    ->orderByDesc('rating_avg')
    ->paginate(12)
    ->withQueryString();
```

Sort is `rating_avg` desc then `bookings_count` desc — no user-facing sort control in Phase 2.
Add a trigram index (`pg_trgm` + GIN) on `headline`/`bio` only if the `ilike` scan measurably
slows down; at demo-data scale it won't.

Deliberately **not** in Phase 2: date-availability filtering, price range, pet species/size,
rating threshold, verified-only, distance radius. The columns exist, so each is a controller
`when()` clause away when it's wanted.

## 7. Views

```
resources/views/
  services/index.blade.php        search bar + category grid
  services/show.blade.php         search bar + provider result cards
  providers/show.blade.php        gallery, bio, services & prices, reviews, sticky booking panel
  bookings/show.blade.php         status timeline, agreed price, actions
  provider/onboarding.blade.php   multi-step: about → location → services & pricing → photos
  provider/bookings.blade.php     inbox: pending requests first, accept/decline inline
  partials/provider-card.blade.php
  partials/service-search.blade.php   keywords + location + service select
  partials/booking-status.blade.php
```

No filter sidebar — `service-search` is a single horizontal bar reused on both pages, so
`services/show` is just the bar plus a result grid. Same warm stone + amber palette;
`provider-card` is the `pet-card` sibling.

Nav: replace the disabled `Services` span in `layouts/app` with a live link, and add
"Become a sitter" to the dashboard.

## 8. Seeders & factories

- `ServiceCategorySeeder` — the seven categories above (idempotent, `updateOrCreate` on slug).
- `ProviderProfileFactory` + `ProviderServiceFactory` + `BookingFactory`.
- `ServiceDemoSeeder` — ~20 providers across several cities with photos, 2–3 services each, and
  bookings spread across every status, so search returns varied results.
- Demo login: `sitter@littlesnoots.test`.

## 9. Build sequence

### 2a — Browse & book ✅ built
Shipped on `feature/services`. 44 feature tests cover search, booking creation, every state
transition and authorization. Tests run against Postgres (`createdb littlesnoots_testing`) because
the search scopes use `ilike`.

Deviations from the plan above, all deliberate:
- **No admin approval queue.** A new profile goes straight to `approved` + published; the
  `pending` status exists in the enum for 2b to use.
- **Photos are curated Wikimedia Commons URLs**, hand-checked so each one depicts the service
  being sold (a dog being groomed, a walk in progress) rather than a generic animal portrait.
  Random-image services were tried and rejected: `picsum.photos` is untyped, and
  `loremflickr.com` keys off user-supplied Flickr tags, which returned a car for `dog,car`,
  infographics for `doggrooming`/`dogtraining` and a paint splash for `rabbit`. Real uploads
  are a 2b item alongside the pet-photo migration.
- **`cancellation_policy` and `instant_book` were dropped**, since neither means anything
  without payments.


1. `service_categories`, `provider_profiles`, `provider_services`, `provider_photos`,
   `provider_unavailable_dates`, `bookings` migrations + models + factories.
2. `ServiceCategorySeeder` + `ServiceDemoSeeder`.
3. Provider onboarding + profile/service management.
4. `/services`, `/services/{category}`, `/sitters/{provider}` with the keyword/location/service
   search bar.
5. Booking form → `StoreBookingRequest` → server-side price calculation → `pending` booking.
6. Provider booking inbox (accept / decline / complete); owner booking view + cancel.
7. Policies, nav link, dashboard sections for both sides.
8. Feature tests: search, booking creation, each state transition, authorization.

### 2b — Trust & reputation
Reviews (completed bookings only, with provider replies) → recompute `rating_avg`/`reviews_count`;
email & phone verification; ID upload + admin approval queue for `pending` profiles; badges;
response rate/time metrics; `owner_pets` profiles; real photo uploads on the `public` disk;
notification emails for every state transition.

### 2c — Messaging
In-app messaging (`conversations` / `messages`) between owner and provider, tied to a booking.

### Later — Payments
Deliberately out of scope for Phase 2. When it comes back: Stripe Connect Express onboarding,
authorise on accept / capture on completion, held payout, platform commission, and a
`cancellation_policy` on the profile driving refunds. The `bookings` price columns are already
shaped for it; it will need new payment columns and a `service_fee`.

## 10. Open questions for later

- Whether providers can bypass the accept step (instant booking) once there's a payment hold to
  make it safe.
- Multi-currency, or MYR/USD only.
- Insurance/guarantee cover — PetBacker's differentiator, but a real underwriting question.
