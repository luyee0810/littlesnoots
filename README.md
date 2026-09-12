# Little Snoots 🐾

A pet-adoption website built with **Laravel 13** and **PostgreSQL**. Browse adoptable pets
with Petfinder-style filters, view rich pet profiles, and submit adoption applications.
Pet **services** and **products** are planned (see [`docs/ROADMAP.md`](docs/ROADMAP.md)).

## Requirements

- PHP 8.5+ and [Composer](https://getcomposer.org)
- Node.js 20+ and npm
- PostgreSQL 14+ (or use SQLite for a zero-setup trial — see below)

## Setup

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Create the database, then edit .env with your DB credentials
createdb littlesnoots           # PostgreSQL — creates the empty database
#   set DB_USERNAME / DB_PASSWORD in .env to match your Postgres user

# 4. Build the schema and demo data
php artisan migrate --seed

# 5. Build front-end assets
npm run build                 # or `npm run dev` for hot-reload while developing

# 6. Run it
php artisan serve             # http://127.0.0.1:8000
```

> **Just want to try it fast?** In `.env` set `DB_CONNECTION=sqlite`, then
> `touch database/database.sqlite && php artisan migrate --seed`. No database server needed.

### Seeded demo accounts
| Email | Role | Password |
|-------|------|----------|
| `admin@littlesnoots.test` | admin | `password` |
| `staff@littlesnoots.test` | staff | `password` |

## Handy commands

```bash
composer run dev                   # server + queue + logs + Vite, all at once
php artisan migrate:fresh --seed   # rebuild the database from scratch
php artisan test                   # run the test suite
./vendor/bin/pint                  # format PHP to the project style
```

## Project layout

- `app/Models` — `Organization`, `Species`, `Breed`, `Pet`, `PetPhoto`, `AdoptionApplication`
- `app/Http/Controllers` — `PetController`, `AdoptionApplicationController`
- `resources/views` — Blade templates (Tailwind CSS v4)
- `database/migrations` · `database/seeders` · `database/factories`
- `docs/ROADMAP.md` — phased plan (adoption → services → products)
- `.claude/agents` — specialised AI agents for this codebase

See [`CLAUDE.md`](CLAUDE.md) for architecture and conventions.
