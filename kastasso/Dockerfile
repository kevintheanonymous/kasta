# Use the official PHP image with Apache
FROM php:8.2-apache

# Install the necessary extensions for your database (PDO MySQL)
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite (needed for your routes.php)
RUN a2enmod rewrite

# Copy all your project files into the container
COPY . /var/www/html/

# Change the "Document Root" of Apache to point to your public folder
# (Because your index.php is inside /kastasso/public, not at the root)
ENV APACHE_DOCUMENT_ROOT /var/www/html/kastasso/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf