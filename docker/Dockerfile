FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libonig-dev libxml2-dev libicu-dev libssl-dev git zip unzip \
    default-mysql-client nginx && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_mysql mbstring bcmath gd && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Xdebug only if it is not already installed
RUN if ! pecl list | grep -q xdebug; then pecl install xdebug && docker-php-ext-enable xdebug; fi && \
    echo "xdebug.mode=debug, coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Copy artisan and boostrap file before composer install (needed for post-install scripts)
COPY artisan ./
COPY bootstrap bootstrap/

# Install PHP dependencies (optimized for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy only necessary application files
COPY app app/
COPY config config/
COPY database database/
COPY public public/
COPY resources resources/
COPY routes routes/
COPY storage storage/

# Set permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Copy Nginx configuration
COPY ./docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

# Copy entrypoint script and make it executable
COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port and set entrypoint
EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start PHP-FPM and Nginx
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
