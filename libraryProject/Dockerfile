FROM php:8.3-fpm-alpine

WORKDIR /app

RUN apk add --no-cache icu-dev libxml2-dev

RUN docker-php-ext-install pdo pdo_mysql soap intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Sadece libraryProject içeriğini kopyala
COPY libraryProject/ .

RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

EXPOSE 8000

CMD php artisan package:discover --ansi && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host=0.0.0.0 --port=8000
