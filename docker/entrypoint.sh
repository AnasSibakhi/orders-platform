#!/usr/bin/env bash
set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Wait for Postgres to accept connections before running migrations.
echo "Waiting for Postgres at ${DB_HOST:-postgres}:${DB_PORT:-5432}..."
until php -r "
    try {
        new PDO('pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        exit(0);
    } catch (\Throwable \$e) {
        exit(1);
    }
"; do
    sleep 2
done
echo "Postgres is up."

if ! grep -q "^APP_KEY=base64" .env; then
    php artisan key:generate --force
fi

php artisan migrate --force
php artisan db:seed --force

echo ""
echo "=================================================="
echo " Ready. Open http://localhost:8000"
echo " Login: \${ADMIN_EMAIL:-admin@example.com}"
echo "=================================================="
echo ""

php artisan serve --host 0.0.0.0 --port 8000
