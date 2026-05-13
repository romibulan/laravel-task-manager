#!/usr/bin/env bash
# Exit on error
set -o errexit

echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Install and build frontend assets (Vite/Mix)
# npm install
# npm run build

echo "Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optional: Run database migrations
# Note: Ensure DB_CONNECTION environment variables are set on Render
echo "Running migrations..."
php artisan migrate --force
