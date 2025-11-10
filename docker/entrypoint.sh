#!/bin/sh
set -e

cd /var/www/html

echo "=== ENTRYPOINT START ==="

if [ ! -f .env ]; then
    echo "[INFO] .env not found, using .env.docker as base"
    if [ -f .env.docker ]; then
        cp .env.docker .env
    else
        echo "[ERROR] .env.docker not found in /var/www/html"
        exit 1
    fi
fi

echo "Loading environment variables from .env..."
if [ -f .env ]; then
    export $(grep -v '^[#[:space:]]' .env | xargs)
fi

echo "APP_ENV is set to: '$APP_ENV'"

echo "Cleaning up old Laravel cache..."
if [ -f bootstrap/cache/config.php ]; then
    rm bootstrap/cache/config.php
fi

echo "Waiting for database connection..."
RETRIES=60
until mysqladmin --skip-ssl --protocol=tcp -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" ping --silent || [ $RETRIES -le 0 ]; do
    echo "Database not ready. Retrying in 5 seconds..."
    RETRIES=$((RETRIES-1))
    sleep 5
done

if [ $RETRIES -le 0 ]; then
    echo "Database did not become ready in time"
    exit 1
fi

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is empty or not set. Generating application key..."
    php artisan key:generate --force

        if [ -f .env ]; then
        export $(grep -v '^[#[:space:]]' .env | xargs)
    fi
else
    echo "APP_KEY is already set. Skipping key:generate."
fi

if [ "$APP_ENV" = "development" ] || [ "$APP_ENV" = "local" ]; then
    echo "Running fresh migrations and seeding for development..."
    php artisan migrate:fresh --seed --force
else
    echo "Running standard migrations..."
    php artisan migrate --force
    php artisan db:seed --force
fi

echo "Caching configuration..."
php artisan config:cache
php artisan route:cache

if [ -L public/storage ]; then
    echo "Removing existing storage link..."
    rm public/storage
fi

echo "Generating storage link..."
php artisan storage:link || true
chmod -R u+w storage

echo "Generating API documentation..."
php artisan l5-swagger:generate || true

echo "Starting PHP-FPM and Nginx..."
exec sh -c "php-fpm & nginx -g 'daemon off;'"




