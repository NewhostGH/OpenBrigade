#!/bin/sh
set -e

# When source is bind-mounted in dev, image files under /var/www/html are masked.
# Restore critical build artifacts from /opt/bootstrap when missing.
if [ ! -f /var/www/html/vendor/autoload.php ] && [ -d /opt/bootstrap/vendor ]; then
	echo "vendor/ missing in bind mount. Restoring from image cache..."
	mkdir -p /var/www/html/vendor
	cp -a /opt/bootstrap/vendor/. /var/www/html/vendor/
fi

if [ ! -f /var/www/html/public/build/manifest.json ] && [ -d /opt/bootstrap/public-build ]; then
	echo "public/build missing in bind mount. Restoring from image cache..."
	mkdir -p /var/www/html/public/build
	cp -a /opt/bootstrap/public-build/. /var/www/html/public/build/
fi

# Ensure writable Laravel directories keep correct permissions even with mounted volumes.
mkdir -p \
	/var/www/html/storage/logs \
	/var/www/html/storage/framework \
	/var/www/html/storage/app/public/uploads \
	/var/www/html/bootstrap/cache
chown -R www-data:www-data \
	/var/www/html/storage/logs \
	/var/www/html/storage/framework \
	/var/www/html/storage/app/public/uploads \
	/var/www/html/bootstrap/cache
chmod -R ug+rwX \
	/var/www/html/storage/logs \
	/var/www/html/storage/framework \
	/var/www/html/storage/app/public/uploads \
	/var/www/html/bootstrap/cache

# Optional auto-migration on startup (enabled by default).
# This ensures required tables like `sessions` exist on first boot.
if [ "${AUTO_RUN_MIGRATIONS:-1}" = "1" ]; then
	echo "Running Laravel migrations..."

	attempt=1
	max_attempts="${MIGRATION_MAX_ATTEMPTS:-30}"

	while [ "$attempt" -le "$max_attempts" ]; do
		if php artisan migrate --force; then
			echo "Migrations completed successfully."
			break
		fi

		if [ "$attempt" -eq "$max_attempts" ]; then
			echo "Migration failed after $max_attempts attempts. Exiting."
			exit 1
		fi

		echo "Migration attempt $attempt/$max_attempts failed. Retrying in 2s..."
		attempt=$((attempt + 1))
		sleep 2
	done
fi

# Start PHP-FPM in background
php-fpm -D

# Run the Laravel scheduler every minute (drives automatic backups, etc.)
( while true; do
	php artisan schedule:run >> /dev/null 2>&1
	sleep 60
done ) &

# Start Nginx in the foreground (keeps the container alive)
exec nginx -g 'daemon off;'
