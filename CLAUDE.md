# Two Fat Cats

Pet adoption website (expanding to pet **services** and **products**). Laravel 13, PostgreSQL.

## Stack
- **Backend:** Laravel 13, PHP 8.5
- **Database:** PostgreSQL — database name `twofatcats`, user `luyee`, local trust auth (no password), port 5432. Configured in `.env` (`DB_CONNECTION=pgsql`).
- **Frontend:** Blade + Tailwind CSS v4 via Vite (`@tailwindcss/vite`), Instrument Sans font.

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

Demo users: `admin@twofatcats.test`, `staff@twofatcats.test` (password = factory default `password`).

## Key paths
- Routes: `routes/web.php`
- Controllers: `app/Http/Controllers`
- Form requests: `app/Http/Requests`
- Views: `resources/views` (layout `layouts/app`, cards `partials/pet-card`)
- Roadmap & future phases: `docs/ROADMAP.md`

## Conventions
- Thin controllers, validation in Form Requests, query logic in Eloquent scopes.
- Case-insensitive search uses Postgres `ilike`.
- Format with `./vendor/bin/pint`; test with `php artisan test`.

## Specialised agents (`.claude/agents/`)
- **design-engineer** — Blade/Tailwind UI & visual design
- **laravel-backend** — controllers, models, business logic
- **database-architect** — migrations, schema, seeders
- **qa-tester** — Pest/PHPUnit tests & verification
- **code-reviewer** — reviews diffs for correctness/security (read-only)
