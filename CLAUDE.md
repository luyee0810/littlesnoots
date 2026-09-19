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
```

## Domain model (Phase 1 — adoption)
- `Organization` (shelter/rescue) → has many `Pet`
- `Species` → `Breed` → `Pet` → `PetPhoto`
- `AdoptionApplication` (belongs to Pet, optionally User)
- `User` roles: `adopter` / `staff` / `admin` (`$user->isStaff()`, `isAdmin()`)
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

## Key paths
- Routes: `routes/web.php`
- Controllers: `app/Http/Controllers`
- Form requests: `app/Http/Requests`
- Views: `resources/views` (layout `layouts/app`, cards `partials/pet-card`)
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
