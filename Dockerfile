# Stage 1: Build Frontend Assets
FROM node:16-alpine as frontend
WORKDIR /app
COPY package.json package-lock.json webpack.mix.js ./
COPY resources ./resources
# Copy existing public assets (images, etc)
COPY public ./public
RUN npm install
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
    --prefer-dist

# Stage 3: Final Production Image
FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip opcache

# Standard PHP settings for Production
RUN { \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

WORKDIR /var/www

# Copy App Code (excludes .dockerignore files)
COPY . /var/www

# Copy Vendor from Stage 2
COPY --from=vendor /app/vendor /var/www/vendor

# Copy Compiled Assets from Stage 1
COPY --from=frontend /app/public/css /var/www/public/css
COPY --from=frontend /app/public/js /var/www/public/js
COPY --from=frontend /app/mix-manifest.json /var/www/public/mix-manifest.json

# Copy .env.example as .env fallback (Production usually injects this, but good for safety)
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Create storage symlink
# Note: In production with volumes, this might need running in an entrypoint
RUN php artisan storage:link || true

# Switch to non-root user
USER www-data

# Expose port and start
EXPOSE 9000
CMD ["php-fpm"]
