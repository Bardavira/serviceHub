#!/bin/sh
set -e

cd /var/www

# Ensure node_modules exists and is writable by the "node" user
mkdir -p /var/www/node_modules
chown -R node:node /var/www/node_modules || true

# Install deps if vite missing
if [ ! -f "node_modules/.bin/vite" ]; then
  echo "[node] Installing npm deps..."
  su-exec node npm install
fi

# Build once for Laravel manifest.json
echo "[node] Building assets (manifest)..."
su-exec node npm run build || true

# Start dev server
echo "[node] Starting Vite dev server..."
exec su-exec node npm run dev -- --host 0.0.0.0 --port 5173