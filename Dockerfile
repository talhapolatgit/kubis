FROM php:8.3-fpm-alpine

WORKDIR /app

# install-php-extensions: çok daha hızlı, RAM dostu
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo pdo_mysql soap intl opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY libraryProject/ .

RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

EXPOSE 8000

CMD php artisan package:discover --ansi && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host=0.0.0.0 --port=8000
