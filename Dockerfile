# ──────────────────────────────────────────────
# OpenBrigade – Multi-stage build
# Composer deps → PHP 8.4 FPM + Nginx
# ──────────────────────────────────────────────

# ── Stage 1: Composer dependencies ────────────
FROM composer:2 AS vendor

WORKDIR /app

# Build-time flag ONLY (controls --dev / --no-dev)
ARG BUILD_ENV=production
ARG COMPOSER_DISABLE_TLS=0
ENV COMPOSER_DISABLE_TLS=${COMPOSER_DISABLE_TLS}
RUN echo "Composer TLS disabled: $COMPOSER_DISABLE_TLS"
RUN echo "Build environment: $BUILD_ENV"

COPY composer.json composer.lock ./

RUN if [ "$BUILD_ENV" = "development" ]; then \
        echo "Installing DEV dependencies"; \
        composer install \
            --no-interaction \
            --no-progress \
            --no-scripts \
            --optimize-autoloader \
            --ignore-platform-reqs; \
    else \
        echo "Installing PROD dependencies"; \
        composer install \
            --no-dev \
            --no-interaction \
            --no-progress \
            --no-scripts \
            --optimize-autoloader \
            --ignore-platform-reqs; \
    fi

RUN echo "Installed Composer packages:" && composer show -i

# ── Stage 1b: Frontend assets (Vite) ──────────
FROM node:22-bookworm-slim AS frontend

WORKDIR /app

ARG NODE_TLS_REJECT_UNAUTHORIZED=1
ENV NODE_TLS_REJECT_UNAUTHORIZED=${NODE_TLS_REJECT_UNAUTHORIZED}

COPY package.json ./
COPY vite.config.js ./
COPY resources ./resources

RUN if [ "$NODE_TLS_REJECT_UNAUTHORIZED" = "0" ]; then npm config set strict-ssl false; else npm config set strict-ssl true; fi
RUN npm install
RUN npm run build

# ── Stage 2: Runtime image ─────────────────────
FROM php:8.4-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        nginx \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libldap2-dev \
        libonig-dev \
        libicu-dev \
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
    && rm -rf /var/lib/apt/lists/*

# PHP config
COPY php.ini /usr/local/etc/php/conf.d/openbrigade.ini

# Nginx config
COPY docker/nginx.conf /etc/nginx/nginx.conf

WORKDIR /var/www/html

# Copy dependencies first (better caching)
COPY --from=vendor /app/vendor ./vendor

# Keep a backup outside /var/www/html so bind mounts do not hide it.
RUN mkdir -p /opt/bootstrap/vendor \
    && cp -a ./vendor/. /opt/bootstrap/vendor/

# Copy built frontend assets
COPY --from=frontend /app/public/build ./public/build

# Keep a backup outside /var/www/html so bind mounts do not hide it.
RUN mkdir -p /opt/bootstrap/public-build \
    && cp -a ./public/build/. /opt/bootstrap/public-build/

# Permissions
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]