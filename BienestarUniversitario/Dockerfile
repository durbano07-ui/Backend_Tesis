# Imagen con PHP-FPM + nginx ya configurados para Laravel (escucha en el puerto 8080)
FROM serversideup/php:8.3.36-fpm-nginx

# Extensiones extra: gd para dompdf (imágenes en PDFs), intl y bcmath
USER root
RUN install-php-extensions gd intl bcmath
USER www-data

WORKDIR /var/www/html

# Dependencias primero para aprovechar la caché de Docker
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY --chown=www-data:www-data . .
RUN composer dump-autoload --optimize --no-dev
