#!/bin/bash
set -e

# Configure Apache port dynamically (Render provides $PORT)
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Export specific env vars to Apache envvars (safe approach - no global dump)
{
  echo "export DATABASE_URL=\"${DATABASE_URL}\""
  echo "export PGHOST=\"${PGHOST}\""
  echo "export PGPORT=\"${PGPORT}\""
  echo "export PGDATABASE=\"${PGDATABASE}\""
  echo "export PGUSER=\"${PGUSER}\""
  echo "export PGPASSWORD=\"${PGPASSWORD}\""
  echo "export MESOMB_APP_KEY=\"${MESOMB_APP_KEY}\""
  echo "export MESOMB_ACCESS_KEY=\"${MESOMB_ACCESS_KEY}\""
  echo "export MESOMB_SECRET_KEY=\"${MESOMB_SECRET_KEY}\""
  echo "export MESOMB_API_URL=\"${MESOMB_API_URL}\""
  echo "export MESOMB_ENABLED=\"${MESOMB_ENABLED}\""
  echo "export ORANGE_ACCOUNT=\"${ORANGE_ACCOUNT}\""
  echo "export MTN_ACCOUNT=\"${MTN_ACCOUNT}\""
} >> /etc/apache2/envvars

# Pass env vars via PassEnv directive so PHP can read them via getenv/$_SERVER
echo "PassEnv DATABASE_URL PGHOST PGPORT PGDATABASE PGUSER PGPASSWORD" >> /etc/apache2/apache2.conf
echo "PassEnv MESOMB_APP_KEY MESOMB_ACCESS_KEY MESOMB_SECRET_KEY MESOMB_API_URL MESOMB_ENABLED" >> /etc/apache2/apache2.conf
echo "PassEnv ORANGE_ACCOUNT MTN_ACCOUNT" >> /etc/apache2/apache2.conf

# Start Apache
exec apache2-foreground
