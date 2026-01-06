# Stage 1: Build Frontend Assets
FROM node:18-alpine as frontend
WORKDIR /app
COPY package.json package-lock.json webpack.mix.js ./
COPY resources ./resources
# Copy existing public assets (images, etc)
COPY public ./public
RUN npm ci
RUN npm run production

# Stage 2: Vendor Dependencies
FROM composer:2 as vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --ignore-platform-reqs \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# Stage 3: Final Production Image
FROM dunglas/frankenphp:php8.2

# Set working directory to /app (standard for FrankenPHP)
WORKDIR /app

# Enable PHP production settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install system dependencies and PHP extensions
# FrankenPHP image comes with install-php-extensions script
RUN install-php-extensions \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    intl

# Configure PHP for Production
RUN { \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    } > $PHP_INI_DIR/conf.d/opcache-recommended.ini

RUN { \
    echo 'memory_limit=512M'; \
    echo 'post_max_size=100M'; \
    echo 'upload_max_filesize=100M'; \
    echo 'max_execution_time=300'; \
    } > $PHP_INI_DIR/conf.d/laravel.ini

# Copy App Code (excludes .dockerignore files)
COPY --chown=root:root . /app

# Copy Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

# Copy Vendor from Stage 2
COPY --from=vendor /app/vendor /app/vendor

# Copy Compiled Assets from Stage 1
COPY --from=frontend /app/public/css /app/public/css
COPY --from=frontend /app/public/js /app/public/js
COPY --from=frontend /app/public/mix-manifest.json /app/public/mix-manifest.json

# Set permissions
# FrankenPHP runs as root by default in container but can switch users.
# For simplicity in this migration, we'll ensure permissions are correct for the web server.
RUN chmod -R 775 /app/storage /app/bootstrap/cache \
    && sed -i 's/\r$//' /app/docker/entrypoint.sh \
    && chmod +x /app/docker/entrypoint.sh

# Clear stale caches and regenerate manifest
RUN rm -f /app/bootstrap/cache/*.php \
    && php artisan package:discover --ansi

# Expose port (FrankenPHP uses 80/443 by default inside)
EXPOSE 80

# Define entrypoint
ENTRYPOINT ["/app/docker/entrypoint.sh"]

