# Little Snoots

Pet adoption website (expanding to pet **services** and **products**). Laravel 13, MySQL.

## Stack
- **Backend:** Laravel 13, PHP 8.5
- **Database:** MySQL 8+ — database name `littlesnoots`, user `root`, no password locally, port 3306.
  Configured in `.env` (`DB_CONNECTION=mysql`). Charset `utf8mb4` / collation `utf8mb4_unicode_ci`.
  Production target is cPanel shared hosting (MySQL), so keep queries portable — no Postgres-only SQL.
- **Frontend:** Blade + Tailwind CSS v4 via Vite (`@tailwindcss/vite`). Figtree carries both
  body and display (display separates by weight/tracking, not a second family), self-hosted
  through the Vite `bunny()` fonts pipeline; Caveat is the script accent, loaded from Google
  in `partials/fonts`.

## Run it
```bash
php artisan serve            # http://127.0.0.1:8000
npm run dev                  # Vite dev server (hot reload) — or `npm run build` for production assets
php artisan migrate:fresh --seed   # rebuild + reseed demo data
php artisan db:seed --class=ReferenceSeeder   # reference data only (species, breeds, categories)
```

Seeders split in two: `ReferenceSeeder` holds what a *real* install needs
(`TaxonomySeeder` species/breeds + `ServiceCategorySeeder`) and is idempotent, so
production runs it alone; `DatabaseSeeder` calls it first, then adds demo content.
Create the first admin with `php artisan user:admin <email> --name="..."` —
production is never seeded, so it otherwise has no accounts.

## Domain model (Phase 1 — adoption)
- `Organization` (shelter/rescue) → has many `Pet`
- `Species` → `Breed` → `Pet` → `PetPhoto`
- `AdoptionApplication` (belongs to Pet, optionally User)
- `User` roles: `adopter` / `staff` / `admin` (`$user->isStaff()`, `isAdmin()`)
- Suspension (`suspended_at`) ends access on the **next request**, not the next login —
  `EnsureUserIsNotSuspended` runs on the whole `web` group. `canBeModeratedBy()` holds the
  guards: no self-moderation, staff can't touch staff, roles are admin-only.
- `Pet` binds routes on `slug`; has `available()` / `published()` scopes and soft deletes.

### Pet attributes — modelled on the Petfinder v2 "animal" object
- Breeds: `breed_id` (primary), `secondary_breed_id`, `breed_mixed`, `breed_unknown` → `breedLabel()`
- Characteristics: `age_group` (baby/young/adult/senior) + optional `age_months`, `gender`, `size`, `coat`, `color` + `secondary_color`
- Attributes: `spayed_neutered`, `shots_current`, `house_trained`, `declawed`, `special_needs`
- Environment ("good in a home with"): `good_with_children`, `good_with_dogs`, `good_with_cats`
- `tags` (json personality tags), `status` (available/pending/adopted/found/unavailable)
- `Organization` = Petfinder "organization": contact, address, `hours` (json), `mission_statement`, `adoption_policy`, socials.

## Domain model (Phase 2a — services marketplace)
PetBacker-style: individuals list services, owners book them directly. Demo data is
Malaysia-based and priced in **MYR (RM)**.

- `ServiceCategory` (Boarding, House Sitting, Dog Walking, Daycare, Grooming, Pet Taxi, Training).
  `pricing_unit` (night/day/walk/session/trip/hour) + `requires_date_range` drive the booking form.
- `ProviderProfile` — 1:1 with `User`, binds routes on `slug`, has `approved()`/`published()`/
  `inCategory()`/`inLocation()`/`search()` scopes. → `ProviderService` (price per category),
  `ProviderPhoto`, `ProviderUnavailableDate`.
- `Booking` — direct booking. `pending` → provider `accepted`/`declined` → `in_progress` →
  `completed`; either side can cancel. Transitions go through guarded model methods
  (`markAccepted()` …) that throw on an illegal move; don't set `status` directly.
- **Provider is not a role.** A user can be an adopter *and* a sitter, which the single-value
  `users.role` enum can't express — `$user->isProvider()` checks for an approved profile.
- **Pet & price are snapshotted onto the booking.** `pets` is the shelter's adoption listing, not
  the owner's pet. Booking totals are always computed server-side in `ProviderService::totalFor()`.
- No payments in Phase 2 — the price is recorded for reference, settled off-platform.
- Search is keywords + location + category only. Other columns exist but aren't facets yet.

Demo users: `admin@littlesnoots.test`, `staff@littlesnoots.test`, `sitter@littlesnoots.test`
(password = factory default `momo12345`).

## Listings & moderation (Phase 2b)
Anyone signed in can rehome a pet — a shelter, a rescuer or a one-off fosterer — so
**listing is a capability, not a role** (like `isProvider()`); ownership is `pets.listed_by`
and `organization_id` stays **optional**.

- `pets.status` describes the *animal* (available/adopted); `pets.review_status` describes
  the *listing* (draft → submitted → approved/rejected). Don't conflate them.
- Approval is what publishes: `markApproved()` sets `published_at`, which `published()` gates
  the public site on. Transitions go through guarded model methods, never `$pet->review_status = …`.
- `good_with_*` are **nullable** — null means "not known yet", which is not "no".
- `/rehome` is the lister's own area (`PetListingController`); `/admin` is staff-only
  (`staff` middleware + `PetPolicy`) and covers listings, sitters and an application overview.
- **Applications are handled by the lister, not staff.** `/rehome/{pet}/applications` is the
  review screen (`AdoptionApplicationPolicy`: lister or staff decide, the applicant may only
  withdraw). Transitions are guarded: pending → reviewing → approved/rejected, and approving
  optionally moves the pet to `pending`/`adopted` — it never assumes it.
- **Sitters are moderated too.** Onboarding creates a `pending` profile — `markApproved()`
  publishes it, `markSuspended()` takes it down with notes the sitter sees on their
  dashboard, and they can `provider.resubmit` after fixing things. `isProvider()` stays
  false until approval, so a pending sitter can still prepare services but isn't bookable.
- Uploads are re-encoded to ≤1600px JPEG by `StorePetPhoto` — shared hosting has a disk quota.

## Messaging
Chat is scoped to a **booking**, not to a pair of users (`booking_messages`): the booking is
what they're discussing, it gives the thread a natural end, and access control is then exactly
`BookingPolicy::view` rather than a second set of rules. Admins can **read** a thread for
disputes but `isParticipant()` stops them posting as a party. Opening the booking page marks
the thread read; `unreadCountFor()` drives the dashboard badge. Messages are `Reportable`.

## Moderation of user-written content
Memorial guestbook messages and sitter reviews use the `Reportable` trait (`reports` table,
polymorphic). Reporting **hides nothing** — it queues the item at `/admin/reports` for a person
to judge; removing content closes every open report on it, and deleting content directly does
the same. Memorial messages are deletable by their author, the memorial's owner (their tribute
page, their call) and staff. A sitter may reply to a review **once** — `canBeRepliedToBy()`
refuses a second, because rewriting a reply a reader already saw isn't a correction.

## Email
Notifications (`app/Notifications`) go out for moderation decisions, adoption enquiries
and booking requests/answers. **Not queued** — shared hosting can't run a worker, so a
queued job would sit in the table forever. Send through `App\Support\Notify`, never
`$user->notify()` directly: it swallows and logs transport errors so a dead mail server
can't turn a successful approval into a 500.

## Key paths
- Routes: `routes/web.php`
- Controllers: `app/Http/Controllers`
- Form requests: `app/Http/Requests`
- Views: `resources/views` (layout `layouts/app`, cards `partials/pet-card`,
  listing form `listings/partials/pet-fields`, admin `admin/`)
- Roadmap & future phases: `docs/ROADMAP.md`

## Conventions
- Thin controllers, validation in Form Requests, query logic in Eloquent scopes.
- Case-insensitive search uses plain `like` — the `utf8mb4_unicode_ci` collation is already
  case-insensitive, so don't reach for `ilike` (Postgres-only) or `LOWER()` wrappers.
- Format with `./vendor/bin/pint`; test with `php artisan test`.
- Tests run against **MySQL**, not sqlite, so they exercise the same collation and SQL as production.
  One-time setup: `mysql -u root -e "create database littlesnoots_testing character set utf8mb4 collate utf8mb4_unicode_ci"`
  (configured in `phpunit.xml`).

## Specialised agents (`.claude/agents/`)
- **design-engineer** — Blade/Tailwind UI & visual design
- **laravel-backend** — controllers, models, business logic
- **database-architect** — migrations, schema, seeders
- **qa-tester** — Pest/PHPUnit tests & verification
- **code-reviewer** — reviews diffs for correctness/security (read-only)
