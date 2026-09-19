#!/usr/bin/env bash
# Build the frontend locally and upload it to the server.
# Shared hosting has no Node, so assets are always built here and shipped up.
#
# Usage: SSH_HOST=user@little-snoots.com bash push-assets.sh

set -euo pipefail
cd "$(dirname "$0")"

: "${SSH_HOST:?Set SSH_HOST, e.g. SSH_HOST=cpaneluser@little-snoots.com}"
REMOTE_PATH="${REMOTE_PATH:-~/littlesnoots}"

echo "==> Building assets locally"
npm run build

echo "==> Uploading public/build to $SSH_HOST:$REMOTE_PATH/public/"
rsync -avz --delete public/build/ "$SSH_HOST:$REMOTE_PATH/public/build/"

echo "==> Done."
