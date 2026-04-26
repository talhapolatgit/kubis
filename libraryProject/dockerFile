FROM php:8.3-fpm-alpine

WORKDIR /app

# Gerekli PHP eklentileri
RUN docker-php-ext-install pdo pdo_mysql

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
