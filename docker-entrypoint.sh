#!/bin/bash
set -e

# Configure Apache port dynamically (Render provides $PORT)
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Export all environment variables to Apache envvars so PHP can access them
env >> /etc/apache2/envvars

# Pass specific env vars via PassEnv directive
echo "PassEnv DATABASE_URL" >> /etc/apache2/apache2.conf
echo "PassEnv PGHOST PGPORT PGDATABASE PGUSER PGPASSWORD" >> /etc/apache2/apache2.conf
echo "PassEnv MESOMB_APP_KEY MESOMB_ACCESS_KEY MESOMB_SECRET_KEY MESOMB_API_URL MESOMB_ENABLED" >> /etc/apache2/apache2.conf
echo "PassEnv ORANGE_ACCOUNT MTN_ACCOUNT" >> /etc/apache2/apache2.conf

# Start Apache
exec apache2-foreground
