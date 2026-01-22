# Use a high-performance PHP/Apache image
FROM richarvey/php-apache-heroku:8.2

# Move into the Laravel folder
COPY . /var/www/app
WORKDIR /var/www/app

# Set the webroot so it points to public/
ENV WEBROOT /var/www/app/public
ENV COMPOSER_ALLOW_SUPERUSER 1

# Install the app dependencies
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build
