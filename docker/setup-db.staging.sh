#!/bin/bash
# Database setup script for CCTV Dashboard - Staging

echo "🚀 Starting database setup for STAGING..."

# Wait for database to be ready
echo "⏳ Waiting for PostgreSQL to be ready..."
until php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; do
  echo "PostgreSQL is unavailable - sleeping"
  sleep 2
done

echo "✅ PostgreSQL is ready!"

# Create database if not exists
echo "📊 Creating database if not exists..."
php artisan db:create || echo "Database might already exist"

# Test database connection
echo "🔗 Testing database connection..."
php artisan tinker --execute="DB::connection()->getPdo();" || exit 1

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Check migration status
echo "📋 Checking migration status..."
php artisan migrate:status

# Run seeders
echo "🌱 Running database seeders..."
php artisan db:seed --force

# Clear cache
echo "🧹 Clearing application cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Setup queue processing with Horizon
echo "⚙️ Setting up Laravel Horizon for STAGING..."
echo "✅ FrankenPHP: 16 workers for web requests"
echo "✅ Horizon: 8 workers for queue processing"
echo "✅ Vite: Development server for hot reload"

# Start Horizon in background
echo "🚀 Starting Laravel Horizon..."
php artisan horizon &
HORIZON_PID=$!

echo "✅ Database setup completed!"
echo "🚀 Starting FrankenPHP with Horizon queue processing..."

# Start FrankenPHP
exec frankenphp run --config /etc/caddy/Caddyfile
