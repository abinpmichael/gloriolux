FROM php:8.2-apache

# Install required PHP extensions for MySQL and other dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy all application files to the Apache document root
COPY . /var/www/html/

# Ensure the vendor directory and other potential cache/upload directories have right permissions
# The uploads folder might need write permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
