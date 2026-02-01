#!/usr/bin/env sh
set -e

cd /var/www

# No frontend? Nothing to do.
if [ ! -f package.json ]; then
  echo "[node] package.json not found, skipping."
  tail -f /dev/null
fi

# Ensure folders exist (volumes may start empty)
mkdir -p node_modules public/build

# Install dependencies only if missing
if [ ! -f node_modules/.bin/vite ]; then
  echo "[node] Installing dependencies..."
  if [ -f package-lock.json ]; then
    npm ci
  else
    npm install
  fi
else
  echo "[node] Dependencies already installed."
fi

echo "[node] Building assets..."
npm run build


# Keep container running (so docker compose up doesn't exit)
tail -f /dev/null