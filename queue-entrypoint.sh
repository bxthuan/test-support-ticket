#!/bin/bash

if [ ! -f ".env" ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
fi

if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

echo "Waiting for database to be ready..."
sleep 5

echo "Starting queue worker..."
exec php artisan queue:work redis --tries=5 --timeout=120 --sleep=3 --max-jobs=1000
