#!/bin/bash

if [ ! -f ".env" ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
fi

if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if [ -z "$(grep APP_KEY= .env | cut -d '=' -f2)" ]; then
    echo "Generating application key..."
    php artisan key:generate --ansi
fi

echo "Running migrations..."
php artisan migrate --force

echo "Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache

exec docker-php-entrypoint php-fpm
