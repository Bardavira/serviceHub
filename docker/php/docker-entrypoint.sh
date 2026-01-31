#!/bin/sh
set -e

cd /var/www

echo "[app] bootstrapping..."

# Ensure required dirs exist (named volumes might start empty)
mkdir -p storage/framework/cache \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache \
         /tmp/composer

# Fix permissions on named volumes (safe & repeatable)
chown -R www-data:www-data storage bootstrap/cache /tmp/composer
chmod -R 775 storage bootstrap/cache /tmp/composer

if [ ! -f "vendor/autoload.php" ]; then
  echo "[app] vendor/autoload.php missing -> composer install"
  su www-data -s /bin/sh -c "COMPOSER_CACHE_DIR=/tmp/composer composer install --no-interaction --prefer-dist"
fi

# Ensure .env exists
if [ ! -f ".env" ]; then
  echo "[app] .env missing -> copying .env.example"
  cp .env.example .env
fi

# Generate APP_KEY if missing/blank
if ! grep -q '^APP_KEY=' .env || grep -q '^APP_KEY=$' .env; then
  echo "[app] APP_KEY missing -> generating"
  su www-data -s /bin/sh -c "php artisan key:generate --force"
fi

# Wait for DB TCP port (no dependency on getenv())
echo "[app] waiting for db:3306..."
until nc -z db 3306; do
  sleep 1
done

# Run migrations
echo "[app] migrating..."
su www-data -s /bin/sh -c "php artisan migrate:fresh --force"

# Seed (challenge-friendly; safe if your seeder is idempotent)
echo "[app] seeding..."
su www-data -s /bin/sh -c "php artisan db:seed --force" || true

# Clear caches (avoid stale config/routes/views)
echo "[app] clearing caches..."
su www-data -s /bin/sh -c "php artisan optimize:clear" || true

echo "[app] ready -> starting php-fpm"
exec "$@"