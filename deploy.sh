#!/usr/bin/env bash
# Little Snoots — deploy on the server (cPanel + SSH).
# Run from the project root on the server:  bash deploy.sh
#
# Assets are NOT built here: shared hosting has no Node. Build them locally with
# `npm run build` and upload public/build/ (see push-assets.sh).

set -euo pipefail

cd "$(dirname "$0")"

# cPanel's default CLI php is old (7.4 on Shinjiru), and 8.5 there ships without
# mbstring/pdo_mysql — so pin the binary that actually has the extensions.
# Override for a different host:  PHP_BIN=/path/to/php bash deploy.sh
PHP_BIN="${PHP_BIN:-/opt/cpanel/ea-php84/root/usr/bin/php}"
[ -x "$PHP_BIN" ] || PHP_BIN="$(command -v php)"

# Composer is not on the PATH under jailshell; prefer the local phar.
if [ -f composer.phar ]; then
  COMPOSER="$PHP_BIN composer.phar"
elif command -v composer >/dev/null 2>&1; then
  COMPOSER="composer"
else
  echo "No composer found. Fetch it with: curl -sS https://getcomposer.org/installer | $PHP_BIN" >&2
  exit 1
fi

if [ ! -f .env ]; then
  echo "No .env found. Copy .env.production.example to .env and fill it in first." >&2
  exit 1
fi

echo "==> PHP: $("$PHP_BIN" -r 'echo PHP_VERSION;')"
"$PHP_BIN" -m | grep -q '^mbstring$' || { echo "PHP is missing mbstring — Laravel needs it." >&2; exit 1; }

echo "==> Pulling latest code"
git pull --ff-only

echo "==> Installing PHP dependencies (production)"
# --no-scripts: shared hosting disables proc_open, which Composer's post-install
# hooks need. package:discover is run directly below instead.
$COMPOSER install --no-dev --optimize-autoloader --no-interaction --no-scripts

echo "==> Discovering packages"
"$PHP_BIN" artisan package:discover

echo "==> Running migrations"
"$PHP_BIN" artisan migrate --force

echo "==> Linking storage (ignored if it already exists)"
"$PHP_BIN" artisan storage:link || true

echo "==> Caching config, routes and views"
"$PHP_BIN" artisan optimize

echo "==> Done. Live at $(grep '^APP_URL=' .env | cut -d= -f2-)"
