FROM php:8.3-apache

# Dépendances système
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Apache
RUN a2enmod rewrite

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# App
WORKDIR /var/www/html
COPY . .

# Symfony prod
RUN composer install --no-dev --optimize-autoloader
RUN chown -R www-data:www-data var

EXPOSE 80
