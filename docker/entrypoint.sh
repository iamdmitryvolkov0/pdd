#!/bin/sh
set -eu

if [ ! -f /var/www/html/docker-config/.env ]; then
    cp /var/www/html/.env.example /var/www/html/docker-config/.env
fi

ln -sf /var/www/html/docker-config/.env /var/www/html/.env
touch /var/www/html/database/database.sqlite

if grep -q '^APP_KEY=$' /var/www/html/docker-config/.env; then
    php artisan key:generate --force --no-interaction
fi

exec "$@"
