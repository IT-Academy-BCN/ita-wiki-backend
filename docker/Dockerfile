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

# Copy all installation files
COPY bootstrap ./bootstrap
COPY public ./public
COPY storage ./storage
COPY src ./src
COPY  artisan composer.json composer.lock phpunit.xml ./

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

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
