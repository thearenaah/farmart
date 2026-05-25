#!/bin/sh

# Fix permissions after volume mount
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
chmod -R 777 /var/www/html/storage

# Link storage
php artisan storage:link --force 2>/dev/null || true

# Volume symlink for uploads persistence
if [ -n "$STORAGE_PATH" ]; then
    mkdir -p "$STORAGE_PATH"
    rm -rf /var/www/html/storage/app/public
    ln -sf "$STORAGE_PATH" /var/www/html/storage/app/public
fi

php artisan migrate --force 2>/dev/null || true
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

PHP_FPM=$(which php-fpm82 || which php-fpm8 || which php-fpm || echo "")
$PHP_FPM -D

nginx -g "daemon off;"

# Create HTMLPurifier cache dir
mkdir -p /var/www/html/storage/app/purifier/HTML
chmod -R 777 /var/www/html/storage/app/purifier
