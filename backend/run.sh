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

