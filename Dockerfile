FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY src/composer.json src/composer.lock* ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist || true

COPY src/ .

RUN composer dump-autoload --optimize || true

RUN if [ ! -f .env ]; then cp .env.example .env; fi

RUN chown -R www-data:www-data /var/www

USER www-data
