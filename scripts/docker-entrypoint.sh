#!/bin/bash
set -e

echo "[entrypoint] Waiting for database..."
for i in $(seq 1 30); do
    if php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USER}', '${DB_PASS}');" 2>/dev/null; then
        echo "[entrypoint] Database ready."
        break
    fi
    sleep 1
done

echo "[entrypoint] Running migrations..."
php /app/scripts/migrate.php

echo "[entrypoint] Starting PHP server on 0.0.0.0:8080..."
php -S 0.0.0.0:8080 -t /app/public
