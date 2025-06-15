# Use the official PHP image with Apache
FROM php:8.2-apache

# Enable Apache mod_rewrite (needed for many PHP apps)
RUN a2enmod rewrite

# Copy project files into the Apache directory
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html
