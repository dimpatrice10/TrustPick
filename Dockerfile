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

# Copier les fichiers du projet
COPY . /var/www/html/

# Créer le dossier logs
RUN mkdir -p /var/www/html/logs && chmod 755 /var/www/html/logs

# Permissions Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Port par défaut (Render fournit $PORT dynamiquement)
ENV PORT=10000
EXPOSE 10000

# Script de démarrage : configure le port + exporte les env vars pour Apache/PHP
CMD /bin/bash -c '\
    sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf && \
    sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf && \
    # Exporter TOUTES les variables d environnement dans Apache envvars pour que PHP puisse y accéder
    env >> /etc/apache2/envvars && \
    # Aussi les passer via PassEnv dans la config Apache
    echo "PassEnv DATABASE_URL" >> /etc/apache2/apache2.conf && \
    echo "PassEnv PGHOST PGPORT PGDATABASE PGUSER PGPASSWORD" >> /etc/apache2/apache2.conf && \
    echo "PassEnv MESOMB_APP_KEY MESOMB_ACCESS_KEY MESOMB_SECRET_KEY MESOMB_API_URL MESOMB_ENABLED" >> /etc/apache2/apache2.conf && \
    echo "PassEnv ORANGE_ACCOUNT MTN_ACCOUNT" >> /etc/apache2/apache2.conf && \
    apache2-foreground'
