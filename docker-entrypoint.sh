#!/bin/bash
set -e

echo "Attente de MySQL..."
while ! mysqladmin ping -h"db" -u"filemanager" -p"filemanager" --silent; do
    sleep 3
    echo "En attente de MySQL..."
done
echo "MySQL est prêt!"

# Configuration Laravel
php artisan key:generate --force
php artisan storage:link
php artisan migrate --force
php artisan config:cache
php artisan route:cache

# Configuration .env pour production
sed -i "s/APP_ENV=local/APP_ENV=production/" .env
sed -i "s/APP_DEBUG=true/APP_DEBUG=false/" .env

echo "Application prête! Démarrage du serveur..."
apache2-foreground