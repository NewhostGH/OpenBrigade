#!/bin/sh
set -e

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in the foreground (keeps the container alive)
exec nginx -g 'daemon off;'
