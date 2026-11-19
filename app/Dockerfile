
# ---------- Stage 1 : dépendances Composer ----------
FROM composer:2 AS composer_build

WORKDIR /app

COPY app/composer.json app/composer.lock ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction

COPY app/ .

RUN composer dump-autoload \
    --optimize \
    --no-dev \
    --classmap-authoritative

# Installation des dépendances ImportMap
RUN php bin/console importmap:install --env=prod


# ---------- Stage 2 : base PHP avec assets compilés ----------
FROM php:8.4-fpm-alpine AS php_base

RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        zip \
        unzip \
        git \
    && docker-php-ext-install \
        pdo_mysql \
        intl \
        opcache \
        zip

WORKDIR /var/www/symfony

COPY --from=composer_build /app ./

ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN mkdir -p var/cache var/log public/assets \
    && chown -R www-data:www-data var public

USER www-data

RUN php bin/console asset-map:compile


# ---------- Stage 3 : image PHP-FPM ----------
FROM php_base AS php

EXPOSE 9000

CMD ["php-fpm"]


# ---------- Stage 4 : image Nginx ----------
FROM nginx:1.27-alpine AS nginx

# Copie des fichiers publics générés dans l'étape PHP
COPY --from=php_base /var/www/symfony/public /var/www/symfony/public

# Configuration Nginx
COPY nginx/build/default.conf /etc/nginx/conf.d/default.conf
