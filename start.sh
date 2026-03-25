#!/bin/bash

# Clear and cache config
echo "Clearing and caching configuration..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Install dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Discover packages
echo "Discovering packages..."
php artisan package:discover

# Generate application key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "Generating application key..."
    php artisan key:generate
fi

# Install Passport keys if they don't exist
if [ ! -f "app/secrets/oauth/oauth-private.key" ]; then
    echo "Installing Passport keys..."
    php artisan passport:install --force
fi

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Run seeders
echo "Running database seeders..."
php artisan db:seed --force

# Generate API documentation
echo "Generating API documentation..."
php artisan l5-swagger:generate



# Start Laravel server
echo "Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=10000