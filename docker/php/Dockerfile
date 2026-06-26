# PHP 8.5.7 (latest) — FPM on Alpine, compatible with Symfony 8.1 (requires PHP >= 8.4)
FROM php:8.5.7-fpm-alpine3.24

# Build/runtime system dependencies
RUN apk add --no-cache \
        git \
        unzip \
        bash

# Convenient, reliable PHP extension installer
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/

# Extensions: Symfony-recommended (intl) + opcache + zip + PostgreSQL (pdo_pgsql)
RUN install-php-extensions \
        intl \
        opcache \
        zip \
        pdo_pgsql

# Composer (latest)
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Recommended PHP settings for Symfony
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.preload_user=www-data'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'realpath_cache_ttl=600'; \
        echo 'memory_limit=256M'; \
    } > /usr/local/etc/php/conf.d/symfony.ini

WORKDIR /var/www/app

EXPOSE 9000
CMD ["php-fpm"]
