#!/bin/sh
set -e

echo "Loading environment variables from .env..."
if [ -f /var/www/html/.env ]; then
    export $(grep -v '^#' /var/www/html/.env | xargs)
fi

echo "APP_ENV is set to: '$APP_ENV'"

echo "Cleaning up old Laravel cache..."
if [ -f /var/www/html/bootstrap/cache/config.php ]; then
    rm /var/www/html/bootstrap/cache/config.php
fi

if [ ! -f /var/www/html/.env ]; then
    echo "[WARNING] - .env File Not Found! Using .env.docker File as .env"
    cp /var/www/html/.env.docker /var/www/html/.env
fi

# Wait for the database to be ready before running migrations
echo "Waiting for database connection..."
until mysqladmin ping -h"$DB_HOST" --silent; do
    echo "Database not ready. Retrying in 5 seconds..."
    sleep 5
done

# Run migrations based on the environment
if [ "$APP_ENV" = "development" ]; then
    echo "Running fresh migrations and seeding..."
    php artisan migrate:fresh --seed --force
else
    echo "Running standard migrations..."
    php artisan migrate --force
    php artisan db:seed --force
fi

echo "Generating application key..."
php artisan key:generate --force

echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
if [ -L /var/www/html/public/storage ]; then
    echo "Removing existing storage link..."
    rm /var/www/html/public/storage
fi

echo "Generating storage link..."
php artisan storage:link
chmod -R u+w storage

echo "Starting PHP-FPM..."
exec php-fpm
