FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    libzip-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip opcache

# Standard PHP settings
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy only composer files first for better caching
COPY composer.json composer.lock /var/www/

# Install composer dependencies
RUN composer install --optimize-autoloader --no-interaction --no-scripts

# Copy the rest of the application code
COPY . /var/www
COPY --chown=www-data:www-data . /var/www

# Run composer scripts now that code is available
RUN composer dump-autoload --optimize

# Copy .env.example as .env if .env doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/.env && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache && \
    chmod 664 /var/www/.env

# Create storage symlink
RUN php artisan storage:link

# Change back to www-data
USER www-data

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
