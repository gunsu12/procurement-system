#!/bin/sh
set -e

echo "Starting application setup (DEV MODE)..."

# Run migrations (if enabled)
if [ "${RUN_MIGRATIONS}" = "true" ]; then
  echo "Running database migrations..."
  php artisan migrate --force
fi

# Clear caches for development to ensure hot reloading works
echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Create storage symlink if it doesn't exist
if [ ! -L /app/public/storage ]; then
  echo "Creating storage symlink..."
  php artisan storage:link
fi

# Ensure correct permissions
# In dev, we often mount from host, so we need to be careful.
# If we are running as root (default in FrankenPHP), we can usually access everything.
# If permissions issues arise, might need chown.
# chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/public

# Start FrankenPHP with --watch for development if supported (depends on binary),
# otherwise just run. Detailed watch is usually for the Go binary itself or Caddyfile changes.
# For PHP file changes, opcache settings handle it.
echo "Starting FrankenPHP..."
exec frankenphp run --config /etc/caddy/Caddyfile
