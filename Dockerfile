# Utiliser l'image PHP officielle avec Apache
FROM php:8.2-apache

# Copier les fichiers de ton projet dans le conteneur
COPY . /var/www/html/

# Activer les modules nécessaires (ex: mysqli si tu utilises MySQL)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Exposer le port que Render utilisera
EXPOSE 10000

# Apache écoute sur 0.0.0.0:10000 via la variable PORT
CMD ["apache2-foreground"]
