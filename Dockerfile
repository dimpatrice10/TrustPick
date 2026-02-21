# Utiliser l'image PHP officielle avec Apache
FROM php:8.2-apache

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libcurl4-openssl-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_pgsql pgsql curl

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Activer mod_rewrite pour Apache
RUN a2enmod rewrite headers

# Configurer Apache pour autoriser les .htaccess (AllowOverride All)
RUN sed -i '/<Directory \/var\/www\/html>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Configurer PHP pour la production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Augmenter les limites PHP
RUN echo "upload_max_filesize = 10M" >> "$PHP_INI_DIR/php.ini" \
    && echo "post_max_size = 10M" >> "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit = 256M" >> "$PHP_INI_DIR/php.ini" \
    && echo "max_execution_time = 60" >> "$PHP_INI_DIR/php.ini"

# Copier d'abord les fichiers Composer pour le cache Docker
COPY composer.json composer.lock /var/www/html/

# Installer les dépendances Composer (MeSomb SDK)
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copier le reste des fichiers du projet
COPY . /var/www/html/

# Rendre le script d'entrée exécutable
RUN chmod +x /var/www/html/docker-entrypoint.sh

# Créer le dossier logs
RUN mkdir -p /var/www/html/logs && chmod 755 /var/www/html/logs

# Permissions Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Port par défaut (Render fournit $PORT dynamiquement)
ENV PORT=10000
EXPOSE 10000

# Script de démarrage
CMD ["/var/www/html/docker-entrypoint.sh"]
