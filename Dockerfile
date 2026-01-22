# Use the official PHP 8.2 Apache image
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
 && docker-php-ext-install pdo pdo_pgsql

# Enable Apache rewrite module (required for Laravel)
RUN a2enmod rewrite

# Set Apache document root to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy application source
COPY . /var/www/html

# Create required Laravel directories and set permissions
RUN mkdir -p \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
 && chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Set working directory
WORKDIR /var/www/html
