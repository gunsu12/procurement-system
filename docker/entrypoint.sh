#!/bin/sh
set -e

echo "Starting application setup..."

# Run migrations (if enabled)
if [ "${RUN_MIGRATIONS}" = "true" ]; then
  echo "Running database migrations..."
  php artisan migrate --force
fi

# Cache configuration for production
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink if it doesn't exist
if [ ! -L /app/public/storage ]; then
  echo "Creating storage symlink..."
  php artisan storage:link
fi

# Ensure correct permissions
# Note: In production with shared volumes, careful with chown if host binds are used.
# If strictly container internal, this is fine.
chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/public

# Start FrankenPHP
echo "Starting FrankenPHP..."
exec frankenphp run --config /etc/caddy/Caddyfile


