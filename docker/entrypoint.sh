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
if [ ! -L /var/www/public/storage ]; then
  echo "Creating storage symlink..."
  php artisan storage:link
fi

# Ensure correct permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public

# Start PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm

