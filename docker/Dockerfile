FROM php:8.2-fpm AS php-stage
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libonig-dev libxml2-dev libicu-dev libssl-dev git zip unzip \
    default-mysql-client && \
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

# Copy the entrypoint and grant execution permissions
COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Use the entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Expose the PHP-FPM port
EXPOSE 9000

# Nginx image
FROM nginx:latest AS nginx-stage
COPY ./nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 8000
CMD ["nginx", "-g", "daemon off;"]
