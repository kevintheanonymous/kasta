FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli zip \
    && a2enmod rewrite

WORKDIR /var/www/html

COPY app/ /var/www/html/app/
COPY config/ /var/www/html/config/
COPY public/ /var/www/html/public/
COPY vendor/ /var/www/html/vendor/
COPY composer.json composer.lock /var/www/html/

RUN chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 80