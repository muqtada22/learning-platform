#!/bin/bash

echo "Running post-deployment tasks..."

# Optimize application
php artisan optimize
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache

# Run migrations
php artisan migrate --force

echo "Post-deployment tasks completed successfully." 