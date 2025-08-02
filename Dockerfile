FROM php:8.1-apache

# Configuration Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN a2enmod rewrite
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev \
    zip unzip git curl default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer le script de démarrage directement dans l'image
RUN echo '#!/bin/bash' > /usr/local/bin/startup.sh \
    && echo 'cd /var/www/html' >> /usr/local/bin/startup.sh \
    && echo 'composer install --no-interaction --no-dev --optimize-autoloader' >> /usr/local/bin/startup.sh \
    && echo 'php artisan storage:link' >> /usr/local/bin/startup.sh \
    && echo 'php artisan migrate --force' >> /usr/local/bin/startup.sh \
    && echo 'php artisan config:cache' >> /usr/local/bin/startup.sh \
    && echo 'php artisan route:cache' >> /usr/local/bin/startup.sh \
    && echo 'chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache' >> /usr/local/bin/startup.sh \
    && echo 'apache2-foreground' >> /usr/local/bin/startup.sh \
    && chmod +x /usr/local/bin/startup.sh

CMD ["/usr/local/bin/startup.sh"]