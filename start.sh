#!/bin/sh
set -e

# Link storage to public if not already linked
php artisan storage:link --force 2>/dev/null || true

# Create symlink from Railway volume to storage if STORAGE_PATH is set
if [ -n "$STORAGE_PATH" ]; then
    mkdir -p "$STORAGE_PATH"
    rm -rf /var/www/html/storage/app/public
    ln -sf "$STORAGE_PATH" /var/www/html/storage/app/public
fi

# Run migrations
php artisan migrate --force 2>/dev/null || true

# Clear and cache config
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Start the server
php-fpm -D
nginx -g "daemon off;"
