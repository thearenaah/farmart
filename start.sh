#!/bin/sh
set -e

# Link storage
php artisan storage:link --force 2>/dev/null || true

# Create symlink from Railway volume to storage if STORAGE_PATH is set
if [ -n "$STORAGE_PATH" ]; then
    mkdir -p "$STORAGE_PATH"
    rm -rf /var/www/html/storage/app/public
    ln -sf "$STORAGE_PATH" /var/www/html/storage/app/public
fi

# Run migrations (skip existing tables)
php artisan migrate --force --skip-existing 2>/dev/null || true

# Clear and cache config
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Find and start php-fpm
PHP_FPM=$(which php-fpm82 || which php-fpm8 || which php-fpm || echo "")
if [ -n "$PHP_FPM" ]; then
    $PHP_FPM -D
else
    echo "ERROR: php-fpm not found"
    exit 1
fi

nginx -g "daemon off;"
