FROM php:8.3-fpm-alpine

WORKDIR /app

# Gerekli sistem kütüphaneleri
RUN apk add --no-cache icu-dev libxml2-dev

# PHP eklentileri
RUN docker-php-ext-install pdo pdo_mysql soap intl

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Proje dosyaları
COPY . .

# Bağımlılıkları yükle
RUN composer install --no-dev --optimize-autoloader

# İzinler
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
