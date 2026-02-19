# Utiliser l'image PHP officielle avec Apache
FROM php:8.2-apache

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_pgsql pgsql curl

# Activer mod_rewrite pour Apache
RUN a2enmod rewrite headers

# Configurer Apache pour écouter sur le port de Render ($PORT)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Configurer Apache pour autoriser les .htaccess (AllowOverride All)
RUN sed -i '/<Directory \/var\/www\/html>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
# Aussi pour /var/www/
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Configurer PHP pour la production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Augmenter les limites PHP
RUN echo "upload_max_filesize = 10M" >> "$PHP_INI_DIR/php.ini" \
    && echo "post_max_size = 10M" >> "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit = 256M" >> "$PHP_INI_DIR/php.ini" \
    && echo "max_execution_time = 60" >> "$PHP_INI_DIR/php.ini"

# Copier les fichiers du projet
COPY . /var/www/html/

# Créer le dossier logs
RUN mkdir -p /var/www/html/logs && chmod 755 /var/www/html/logs

# Permissions Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exposer le port (Render utilise la variable PORT)
EXPOSE ${PORT}

# Variable d'environnement par défaut pour le port
ENV PORT=10000

# Démarrer Apache
CMD ["apache2-foreground"]
