#!/bin/sh

# Fix permissions after volume mount
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache

# Create required directories
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/app/purifier/HTML
chmod -R 777 /var/www/html/storage

# Persist platform/plugins on volume
if [ -n "$STORAGE_PATH" ]; then
    mkdir -p /data/plugins
    # Copy built-in plugins to volume on first run
    if [ ! -f /data/plugins/.initialized ]; then
        cp -r /var/www/html/platform/plugins/. /data/plugins/
        touch /data/plugins/.initialized
    fi
    # Mount volume plugins over container plugins
    rm -rf /var/www/html/platform/plugins
    ln -sf /data/plugins /var/www/html/platform/plugins
fi

# Link storage
php artisan storage:link --force 2>/dev/null || true

# Volume symlink for uploads persistence
mkdir -p /data/storage
rm -rf /var/www/html/storage/app/public
ln -sf /data/storage /var/www/html/storage/app/public

php artisan migrate --force 2>/dev/null || true
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

PHP_FPM=$(which php-fpm82 || which php-fpm8 || which php-fpm || echo "")
$PHP_FPM -D

nginx -g "daemon off;"
