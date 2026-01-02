#!/usr/bin/env bash
set -euo pipefail

echo "Running container entrypoint..."

# Install PHP deps if needed
if [ -f composer.json ]; then
  composer install --no-interaction || true
fi

# Wait for DB to be ready
echo "Waiting for DB to be available..."
max_retries=30
count=0
until php -r "try { new PDO(sprintf('mysql:host=%s;port=%s', getenv('DB_HOST')?:'db', getenv('DB_PORT')?:'3306'), getenv('DB_USER')?:'root', getenv('DB_PASS')?:''); echo 'ok'; } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
  count=$((count+1))
  if [ "$count" -ge "$max_retries" ]; then
    echo "DB did not become available in time" >&2
    break
  fi
  sleep 1
done

# Run migrations and seed
echo "Running migrations..."
php cli/migrate.php || true

echo "Seeding default data..."
php cli/seed.php || true

echo "Starting PHP built-in server..."
php -S 0.0.0.0:8080 -t public
