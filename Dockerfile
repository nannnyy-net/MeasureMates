FROM php:8.2-cli

# Install OS dependencies + PHP extensions required by Laravel (and by requirement list)
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    pkg-config \
    unzip \
  && docker-php-ext-configure zip \
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
  && docker-php-ext-enable pdo_mysql mysqli \
  && rm -rf /var/lib/apt/lists/*

# Optional but commonly needed for Laravel performance
RUN docker-php-ext-install opcache \
  && echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini

# Create non-root user (Railway runs containers as non-root in some templates)
RUN useradd -m -u 10001 appuser
WORKDIR /app

# Copy only composer manifests first for better layer caching
COPY composer.json composer.lock /app/

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# IMPORTANT: disable Composer scripts during dependency install.
# Laravel's post-autoload-dump runs `@php artisan package:discover`, which requires `artisan`
# to exist. `artisan` isn't present until after the full app is copied.
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader

# Copy application code (includes artisan)
COPY . /app

# Re-run autoload generation now that artisan exists (allows Laravel package discovery)
RUN composer dump-autoload --optimize



# Install Node.js + npm for Vite asset compilation (Railway Docker build image doesn't include Node)
RUN apt-get update \
  && apt-get install -y --no-install-recommends nodejs npm \
  && rm -rf /var/lib/apt/lists/*

# Build frontend assets (Vite)
RUN if [ -f package.json ] && [ -f package-lock.json ]; then \
      npm ci --no-audit --no-fund && npm run build; \
    else \
      echo "package.json or package-lock.json missing; skipping frontend build"; \
    fi

# Create Laravel storage symlink during image build
RUN php artisan storage:link --force || true

# Production hardening
RUN php artisan optimize:clear --no-interaction || true

# Fix permissions
RUN chown -R appuser:appuser /app/storage /app/bootstrap/cache /app/public

USER appuser

EXPOSE 8080

# Railway uses PORT env var
ENV PORT=8080

# Run migrations+cache then start
CMD ["sh", "-lc", "php artisan storage:link --force && php artisan migrate --force && php artisan optimize:clear --no-interaction && php artisan optimize --no-interaction && php -S 0.0.0.0:${PORT:-8080} -t public"]


