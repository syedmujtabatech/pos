#!/usr/bin/env bash
# POS System - 1-Click Linux / macOS Launcher

echo "Starting POS System..."

if [ ! -f ".env" ]; then
    echo "Creating .env..."
    cp .env.example .env
    composer install --no-interaction
    php artisan key:generate --force
    touch database/database.sqlite
    php artisan migrate:fresh --seed --force
fi

php artisan serve --host=127.0.0.1 --port=8000
