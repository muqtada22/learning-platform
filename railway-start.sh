#!/bin/bash
set -e

echo "🔄 Starting deployment process..."

# Environment check
echo "🔍 Environment: $APP_ENV"
echo "📂 Directory contents:"
ls -la

# Check PHP
echo "🔍 PHP Version:"
php -v

# Create database schema (fallback for migrations)
if [ -f "database/schema/mysql-schema.sql" ]; then
  echo "📄 Found schema file, applying..."
  # Try to use it but don't fail if there's an error
  mysql -h "$DATABASE_URL" -u "$MYSQLUSERNAME" -p"$MYSQLPASSWORD" "$MYSQLDATABASE" < database/schema/mysql-schema.sql || echo "⚠️ Schema import failed, continuing anyway"
fi

# Cache configuration
echo "📋 Caching configuration..."
php artisan config:clear
php artisan config:cache || echo "⚠️ Config cache failed"
php artisan route:clear
php artisan route:cache || echo "⚠️ Route cache failed"

# Run migrations
echo "🔄 Running migrations..."
php artisan migrate --force || echo "⚠️ Migrations failed"

# Start the web server
echo "🚀 Starting web server..."
if [ -f "vendor/bin/heroku-php-apache2" ]; then
  echo "🌐 Using Heroku PHP Apache"
  vendor/bin/heroku-php-apache2 public/
else
  echo "🌐 Using PHP built-in server"
  php -S 0.0.0.0:"${PORT:-8080}" -t public
fi 