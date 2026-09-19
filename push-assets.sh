#!/usr/bin/env bash
# Build the frontend and ship it to the server.
#
# Shinjiru allows no inbound SSH (port 22 is refused), so rsync/scp can't reach
# the server — assets travel through Git instead. public/build is committed on
# purpose: the server has no Node and cannot build them itself.
#
# Usage: bash push-assets.sh          then, in cPanel Terminal: bash deploy.sh

set -euo pipefail
cd "$(dirname "$0")"

echo "==> Building assets"
npm run build

if git diff --quiet -- public/build; then
  echo "==> No asset changes to push."
  exit 0
fi

echo "==> Committing built assets"
git add public/build
git commit -m "Rebuild frontend assets"

echo "==> Pushing"
git push origin "$(git rev-parse --abbrev-ref HEAD)"

echo
echo "==> Now run this in the cPanel Terminal:"
echo "    cd ~/littlesnoots && bash deploy.sh"
