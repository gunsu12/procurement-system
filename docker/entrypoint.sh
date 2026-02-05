#!/bin/sh
set -e

echo "Starting application setup..."

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

# Start Supervisor (which manages FrankenPHP and Scheduler)
echo "Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf


