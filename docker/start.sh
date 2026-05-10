#!/usr/bin/env sh
set -eu

mkdir -p /var/www/html/storage/app
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache

chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

touch /var/www/html/storage/app/database.sqlite

php artisan config:clear
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=8000
