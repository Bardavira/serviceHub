#!/usr/bin/env sh
set -e

cd /var/www

# Always ensure runtime dirs exist
mkdir -p \
  storage/app \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

# Ensure writable dirs are owned by the php-fpm user (www-data)
chown -R www-data:www-data storage bootstrap/cache >/dev/null 2>&1 || true
chmod -R 775 storage bootstrap/cache >/dev/null 2>&1 || true

# .env bootstrap (needed for artisan)
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
  else
    echo "ERROR: .env missing and .env.example not found" >&2
    exit 1
  fi
fi

ROLE="${CONTAINER_ROLE:-app}"

if [ "$ROLE" = "queue" ]; then
  echo "[queue] waiting for vendor/autoload.php (composer runs in app)..."
  for i in $(seq 1 300); do
    [ -f vendor/autoload.php ] && break
    sleep 1
  done

  if [ ! -f vendor/autoload.php ]; then
    echo "[queue] vendor/autoload.php still missing after wait, exiting" >&2
    exit 1
  fi

  echo "[queue] all set"

  exec "$@"
fi

# ROLE = app
if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

echo "[app] waiting for DB..."
for i in $(seq 1 60); do
  if mysql -h"${DB_HOST:-db}" -u"${DB_USERNAME:-servicehub}" -p"${DB_PASSWORD:-secret}" \
    -e "SELECT 1" "${DB_DATABASE:-servicehub}" >/dev/null 2>&1; then
    break
  fi
  sleep 1
done

php artisan key:generate --force >/dev/null 2>&1 || true
php artisan migrate --seed --force || true
php artisan optimize:clear >/dev/null 2>&1 || true

echo "[app] all set"

exec "$@"