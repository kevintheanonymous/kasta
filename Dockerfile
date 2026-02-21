FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev

RUN docker-php-ext-install pdo pdo_mysql mysqli zip

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80