#!/bin/sh
set -e

echo "Starting application setup (DEV MODE)..."

# Check Minio connection
if [ -n "$AWS_ENDPOINT" ]; then
  echo "Checking Minio connection..."
  
  # Extract Host
  AWS_HOST=$(echo $AWS_ENDPOINT | sed -e 's|^[^/]*//||' -e 's|:.*$||' -e 's|/.*$||')
  
  # Extract Port or assign default based on protocol
  TEMP_PORT=$(echo $AWS_ENDPOINT | grep -o ':[0-9]\+' | sed 's/://')
  
  if [ -n "$AWS_PORT" ]; then
    # Use explicitly defined AWS_PORT if provided
    TARGET_PORT=$AWS_PORT
  elif [ -n "$TEMP_PORT" ]; then
    # Use port from ENDPOINT if present
    TARGET_PORT=$TEMP_PORT
  else
    # Default based on protocol
    case "$AWS_ENDPOINT" in
      https://*) TARGET_PORT=443 ;;
      *)         TARGET_PORT=80 ;;
    esac
  fi
  
  if php -r "\$s=@fsockopen(\"$AWS_HOST\", (int)\"$TARGET_PORT\", \$errno, \$errstr, 5); if(!\$s){exit(1);}" ; then
    echo "✅ Minio connection SUCCESSFUL ($AWS_HOST:$TARGET_PORT)"
  else
    echo "❌ Minio connection FAILED ($AWS_HOST:$TARGET_PORT)"
    echo "Check if $AWS_ENDPOINT is reachable from this container."
  fi
fi

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
