#!/bin/bash
set -e

# Function to handle cleanup on exit
cleanup() {
    echo "Shutting down..."
    kill -TERM "$FRANKENPHP_PID" 2>/dev/null || true
    php artisan horizon:terminate 2>/dev/null || true
    exit 0
}

trap cleanup SIGTERM SIGINT

# Start FrankenPHP in background
echo "Starting FrankenPHP..."
frankenphp run --config /etc/caddy/Caddyfile &
FRANKENPHP_PID=$!

# Wait a moment for FrankenPHP to start
sleep 2

# Start Laravel Horizon in foreground (this keeps container alive)
# Horizon sudah memiliki internal supervisor untuk mengelola worker processes
echo "Starting Laravel Horizon..."
php artisan horizon

