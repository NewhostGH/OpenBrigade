#!/bin/sh
set -e

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

# Start Nginx in the foreground (keeps the container alive)
exec nginx -g 'daemon off;'
