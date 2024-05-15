#!/bin/sh

#Stop queue job worker
pm2 stop queue-worker

# Change to the project directory.
cd ~/public_html/lumminn

# Pull the latest changes from the git repository
git pull origin main

# Install/update composer dependencies
composer install --no-interaction

# Build vite
npm run build

# Run database migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear

# Clear and cache routes
php artisan route:cache

# Clear and cache config
php artisan config:cache

# Clear and cache views
php artisan view:cache

# Clear and cache events
php artisan event:cache

#Stop queue job worker
pm2 start queue-worker
