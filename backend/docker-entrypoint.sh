#!/bin/sh
set -e

# Gerar APP_KEY se não existir
grep -q "^APP_KEY=$" .env 2>/dev/null && php artisan key:generate

echo "Waiting for MySQL..."
until php artisan migrate --force 2>/dev/null; do
  echo "MySQL not ready, retrying in 3s..."
  sleep 3
done

echo "Starting server..."
php artisan serve --host=0.0.0.0
