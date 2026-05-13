#!/bin/sh

# Exit immediately if a command fails
set -e

echo "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running migrations..."
# --force is required in production
php artisan migrate --force

echo "Starting Nginx and PHP-FPM..."
# This command depends on your base image's supervisor/process manager
exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
