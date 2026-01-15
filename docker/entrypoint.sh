#!/bin/sh
set -e

echo "Starting application setup..."

# Check Minio connection
if [ -n "$AWS_ENDPOINT" ]; then
  echo "Checking Minio connection..."
  AWS_HOST=$(echo $AWS_ENDPOINT | sed -e 's|^[^/]*//||' -e 's|:.*$||')
  AWS_PORT=$(echo $AWS_ENDPOINT | sed -e 's|^.*:||' -e 's|/.*$||')
  # Default port to 80 if not found
  if [ "$AWS_PORT" = "$AWS_HOST" ]; then
    AWS_PORT=80
  fi
  
  if php -r "\$s=@fsockopen(\"$AWS_HOST\", (int)\"$AWS_PORT\", \$errno, \$errstr, 5); if(!\$s){exit(1);}" ; then
    echo "✅ Minio connection SUCCESSFUL ($AWS_HOST:$AWS_PORT)"
  else
    echo "❌ Minio connection FAILED ($AWS_HOST:$AWS_PORT)"
  fi
fi

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


