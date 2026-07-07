FROM php:8.3-cli-alpine

WORKDIR /app

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo pdo_mysql soap intl opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY libraryProject/ .

RUN rm -f .env

RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN mkdir -p storage/framework/{cache,sessions,views} storage/app/public bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD php artisan package:discover --ansi && \
    php artisan migrate --force && \
    php artisan storage:link --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan serve --host=0.0.0.0 --port=8000
