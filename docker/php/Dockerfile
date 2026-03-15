FROM php:8.4-fpm-alpine

# Системные зависимости
RUN apk add --no-cache \
    git \
    unzip \
    libpq-dev \
    icu-dev

# PHP-расширения
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN apk add --no-cache postgresql-client bash

COPY wait-for-db.sh /usr/local/bin/wait-for-db.sh
RUN chmod +x /usr/local/bin/wait-for-db.sh

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]