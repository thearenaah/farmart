FROM dinhquochan/laravel:php8.2

WORKDIR /var/www/html

RUN apk add --no-cache php82-calendar && \
    echo "extension=calendar.so" > /etc/php82/conf.d/00_calendar.ini

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN npm install && npm run production 2>/dev/null || true

# Publish all vendor assets into public/ at build time
RUN php artisan vendor:publish --all --force 2>/dev/null || true

RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache && \
    chmod -R 777 storage bootstrap/cache

EXPOSE 80
