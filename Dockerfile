FROM dinhquochan/laravel:php8.2

WORKDIR /var/www/html

RUN apk add --no-cache php82-calendar && \
    echo "extension=calendar.so" > /etc/php82/conf.d/00_calendar.ini

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN npm install && npm run production 2>/dev/null || true

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
