#!/usr/bin/env bash
# Little Snoots — deploy on the server (cPanel + SSH).
# Run from the project root on the server:  bash deploy.sh
#
# Assets are NOT built here: shared hosting has no Node. Build them locally with
# `npm run build` and upload public/build/ (see push-assets.sh).

set -euo pipefail

cd "$(dirname "$0")"

if [ ! -f .env ]; then
  echo "No .env found. Copy .env.production.example to .env and fill it in first." >&2
  exit 1
fi

echo "==> Pulling latest code"
git pull --ff-only

echo "==> Installing PHP dependencies (production)"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Running migrations"
php artisan migrate --force

echo "==> Linking storage (ignored if it already exists)"
php artisan storage:link || true

echo "==> Caching config, routes and views"
php artisan optimize

echo "==> Done. Live at $(grep '^APP_URL=' .env | cut -d= -f2-)"
