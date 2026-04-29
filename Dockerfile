# ──────────────────────────────────────────────
# OpenBrigade – Dockerfile
# PHP 8.1 + Apache
# ──────────────────────────────────────────────
FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libldap2-dev \
    libonig-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu \
    && docker-php-ext-install \
        mysqli \
        pdo \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        ldap

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Apply custom php.ini settings
COPY php.ini /usr/local/etc/php/conf.d/openbrigade.ini

# Set working directory
WORKDIR /var/www/html

# Copy application source
COPY . .

# Ensure user-data and conf directories are writable
RUN chown -R www-data:www-data /var/www/html/user-data /var/www/html/conf \
    && chmod -R 775 /var/www/html/user-data /var/www/html/conf

# Apache: allow .htaccess overrides in document root
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

EXPOSE 80
