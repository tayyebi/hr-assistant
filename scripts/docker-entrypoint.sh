#!/bin/bash
set -e

DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_NAME="${DB_NAME:-app}"

echo "[entrypoint] Waiting for database at ${DB_HOST}:${DB_PORT}..."

# Wait for database with exponential backoff
WAIT_COUNT=0
MAX_ATTEMPTS=60

while [ $WAIT_COUNT -lt $MAX_ATTEMPTS ]; do
    if php -r "
        try {
            \$pdo = new PDO(
                'mysql:host=${DB_HOST};port=${DB_PORT}',
                '${DB_USER}',
                '${DB_PASS}',
                [PDO::ATTR_TIMEOUT => 5]
            );
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null; then
        echo "[entrypoint] Database is ready!"
        break
    fi
    
    WAIT_COUNT=$((WAIT_COUNT + 1))
    SLEEP_TIME=$((WAIT_COUNT / 10 + 1))
    echo "[entrypoint] Database not ready yet (attempt $WAIT_COUNT/$MAX_ATTEMPTS). Waiting ${SLEEP_TIME}s..."
    sleep $SLEEP_TIME
done

if [ $WAIT_COUNT -eq $MAX_ATTEMPTS ]; then
    echo "[entrypoint] ERROR: Database failed to become ready after $((MAX_ATTEMPTS * 2))s"
    exit 1
fi

echo "[entrypoint] Running migrations..."
php /app/scripts/migrate.php || {
    echo "[entrypoint] ERROR: Migration failed"
    exit 1
}

echo "[entrypoint] Seeding database..."
php /app/scripts/seed.php || {
    echo "[entrypoint] Warning: Seeding failed (may already be seeded)"
}

echo "[entrypoint] Starting PHP server on 0.0.0.0:8080..."
php -S 0.0.0.0:8080 -t /app/public
