FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev \
 && docker-php-ext-install zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

CMD sh -c "php -S 0.0.0.0:${PORT:-10000} -t public"
