# ──────────────────────────────────────────────
# OpenBrigade – Dockerfile
# Multi-stage build: Composer deps → PHP 8.4 FPM Alpine + Nginx
# ──────────────────────────────────────────────

# ── Stage 1: Composer dependency install ──────
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader \
    --ignore-platform-reqs

# ── Stage 2: Runtime image ─────────────────────
FROM php:8.4-fpm-alpine

# Install runtime system dependencies
RUN apk add --no-cache \
    nginx \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype \
    openldap \
    oniguruma \
    icu-libs \
    && apk add --no-cache --virtual .build-deps \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        openldap-dev \
        oniguruma-dev \
        icu-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        mysqli \
        pdo \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        ldap \
        intl \
        opcache \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

# Apply PHP settings
COPY php.ini /usr/local/etc/php/conf.d/openbrigade.ini

# Nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Application root
WORKDIR /var/www/html

# Copy vendor from build stage
COPY --from=vendor /app/vendor ./vendor

# Copy application source (vendor/ is already present from above)
COPY . .

# Generate optimised autoloader inside the image
RUN composer dump-autoload --optimize --no-dev 2>/dev/null || true

# Storage and bootstrap/cache must be writable by www-data (Alpine: uid 82)
RUN chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R 775 \
        storage \
        bootstrap/cache

# Expose only the public/ directory via Nginx
EXPOSE 80

# Start Nginx + PHP-FPM
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]

