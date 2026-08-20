FROM php:8.2-apache

# System deps needed to build the redis/mongodb PECL extensions
RUN apt-get update && apt-get install -y \
        libzip-dev \
        unzip \
        git \
    && rm -rf /var/lib/apt/lists/*

# MySQL (via PDO) support
RUN docker-php-ext-install pdo pdo_mysql

# Redis + MongoDB PHP extensions (used by php/redis.php and php/mongo.php)
RUN pecl install redis mongodb \
    && docker-php-ext-enable redis mongodb

RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html/

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Render injects the actual port to listen on via $PORT at runtime.
ENV PORT=10000
EXPOSE 10000

ENTRYPOINT ["/entrypoint.sh"]
