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
FROM php:8.1-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip opcache

# Configure PHP for Production
RUN { \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

RUN { \
    echo 'memory_limit=512M'; \
    echo 'post_max_size=100M'; \
    echo 'upload_max_filesize=100M'; \
    echo 'max_execution_time=300'; \
    } > /usr/local/etc/php/conf.d/laravel.ini

WORKDIR /var/www

# Copy App Code (excludes .dockerignore files)
COPY --chown=www-data:www-data . /var/www

# Copy Vendor from Stage 2
COPY --from=vendor --chown=www-data:www-data /app/vendor /var/www/vendor

# Copy Compiled Assets from Stage 1
COPY --from=frontend --chown=www-data:www-data /app/public/css /var/www/public/css
COPY --from=frontend --chown=www-data:www-data /app/public/js /var/www/public/js
COPY --from=frontend --chown=www-data:www-data /app/public/mix-manifest.json /var/www/public/mix-manifest.json

# Set permissions and ensure entrypoint is executable
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
    && chmod +x /var/www/docker/entrypoint.sh

# Clear stale caches and regenerate manifest
RUN rm -f /var/www/bootstrap/cache/*.php \
    && php artisan package:discover --ansi

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php-fpm -t || exit 1

# Switch to non-root user
USER www-data

# Expose port and start
EXPOSE 9000
ENTRYPOINT ["/var/www/docker/entrypoint.sh"]

