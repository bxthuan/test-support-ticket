#!/bin/bash

echo "Waiting for app container to install composer..."
while [ ! -f "vendor/autoload.php" ]; do
    sleep 2
done

echo "Waiting for database to be ready..."
sleep 10

echo "Starting queue worker..."
exec php artisan queue:work redis --tries=5 --timeout=120 --sleep=3 --max-jobs=1000
