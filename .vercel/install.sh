#!/bin/bash

# Install Composer dependencies
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Create necessary directories
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set permissions
chmod -R 775 storage bootstrap/cache
