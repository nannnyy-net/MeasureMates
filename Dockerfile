FROM php:8.2-cli

# Install system dependencies
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        zip \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        nodejs \
        npm \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        mbstring \
        bcmath \
        exif \
        pcntl \
        zip \
        xml \
        opcache \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Composer files first
COPY composer.json composer.lock ./

# Install PHP dependencies without running Laravel scripts yet
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# Copy the application
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Run Laravel package discovery
RUN php artisan package:discover --ansi

# Build frontend assets
RUN if [ -f package.json ]; then \
      npm ci && npm run build; \
    fi

# Create required directories
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Create storage symlink
RUN php artisan storage:link || true

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8080

ENV PORT=8080

CMD sh -c "\
php artisan optimize:clear && \
php artisan migrate --force && \
php -S 0.0.0.0:${PORT:-8080} -t public"
