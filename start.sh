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

# Restore any user-uploaded plugins from volume back into container
mkdir -p /data/plugins
for plugin_dir in /data/plugins/*/; do
    plugin_name=$(basename "$plugin_dir")
    if [ ! -d "/var/www/html/platform/plugins/$plugin_name" ]; then
        cp -r "$plugin_dir" "/var/www/html/platform/plugins/$plugin_name"
    fi
done
chmod -R 777 /var/www/html/platform/plugins

# Hook: after a plugin is uploaded, also save it to volume
# (This is handled via a post-upload script or we watch the dir)
# For now, sync container plugins to volume that aren't already there
for plugin_dir in /var/www/html/platform/plugins/*/; do
    plugin_name=$(basename "$plugin_dir")
    if [ ! -d "/data/plugins/$plugin_name" ]; then
        cp -r "$plugin_dir" "/data/plugins/$plugin_name"
    fi
done

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