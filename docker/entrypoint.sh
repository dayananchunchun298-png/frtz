#!/bin/sh
# Do not use set -e — a failed migration or asset compile must not prevent the web server from starting.

cd /app

# Railway public URL when APP_URL is not set explicitly
if [ -z "${APP_URL:-}" ] && [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ]; then
    export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
    export DEFAULT_URI="${APP_URL}"
fi

PORT="${PORT:-8000}"
PHP_RUNTIME="${PHP_RUNTIME:-auto}"

# Symfony only recognizes "prod", not "production"
case "${APP_ENV:-prod}" in
    production) export APP_ENV=prod ;;
esac

# Keep DEFAULT_URI in sync with APP_URL for routing / emails
if [ -n "${APP_URL:-}" ]; then
    export DEFAULT_URI="${APP_URL}"
fi

# Ensure .env exists (image ships .env.dist; compose may mount host .env)
if [ ! -f .env ]; then
    cp .env.dist .env
fi

is_railway() {
    [ -n "${RAILWAY_SERVICE_ID:-}" ] \
        || [ -n "${RAILWAY_ENVIRONMENT:-}" ] \
        || [ -n "${RAILWAY_ENVIRONMENT_NAME:-}" ] \
        || [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ] \
        || [ -n "${RAILWAY_REPLICA_ID:-}" ]
}

mkdir -p var/cache var/log var/sessions public/uploads
chmod -R ug+rwX var public/uploads 2>/dev/null || true

echo "Waiting for database..."
i=0
while [ $i -lt 60 ]; do
    if php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; then
        echo "Database is ready."
        break
    fi
    i=$((i + 1))
    sleep 1
done
if [ $i -ge 60 ]; then
    echo "WARNING: database not reachable after 60s — continuing (set DATABASE_URL in Railway and link MySQL)."
fi

echo "Running database migrations..."
if ! php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration; then
    echo "WARNING: migrations failed — check DATABASE_URL and MySQL service."
fi

if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then
    echo "Generating JWT key pair..."
    php bin/console lexik:jwt:generate-keypair --skip-if-exists || echo "WARNING: JWT key generation failed."
fi

if [ "${APP_ENV:-prod}" = "prod" ]; then
    echo "Warming production cache..."
    php bin/console cache:clear --no-warmup || true
    if ! php bin/console cache:warmup; then
        echo "WARNING: cache warmup failed — check APP_SECRET and env vars."
    fi
    php bin/console assets:install public --no-interaction 2>/dev/null || true
    if [ ! -f public/assets/importmap.json ]; then
        echo "Compiled assets missing — preparing front-end for production..."
        if [ ! -f assets/vendor/installed.php ]; then
            php bin/console importmap:install --no-interaction || echo "WARNING: importmap:install failed (CDN may be unreachable)."
        fi
        php bin/console asset-map:compile || echo "WARNING: asset-map:compile failed."
    fi
fi

chown -R www-data:www-data var public/uploads 2>/dev/null || true
chmod -R ug+rwX var public/uploads 2>/dev/null || true
if [ -d config/jwt ]; then
    chown -R www-data:www-data config/jwt 2>/dev/null || true
    chmod -R ug+rwX config/jwt 2>/dev/null || true
fi

if is_railway; then
    echo "Railway: starting PHP on 0.0.0.0:${PORT}..."
    exec php -S "0.0.0.0:${PORT}" -t public public/router.php
fi

use_fpm=0
if [ "$PHP_RUNTIME" = "fpm" ]; then
    use_fpm=1
elif [ "$PHP_RUNTIME" = "builtin" ]; then
    use_fpm=0
else
    php-fpm 2>/dev/null || true
    sleep 2
    if ss -tln 2>/dev/null | grep -q ':9000'; then
        use_fpm=1
    else
        killall php-fpm 2>/dev/null || true
    fi
fi

if [ "$use_fpm" = "1" ]; then
    echo "Starting PHP-FPM..."
    if [ "$PHP_RUNTIME" != "fpm" ]; then
        : # already started during auto-detect (daemon mode)
    else
        php-fpm
        sleep 2
    fi
    cp /etc/nginx/default-fpm.conf /etc/nginx/conf.d/default.conf
else
    echo "Starting PHP built-in server (PHP-FPM unavailable on this host)..."
    php -S 127.0.0.1:9080 -t public public/index.php >/dev/null 2>&1 &
    sleep 1
    cp /etc/nginx/default-builtin.conf /etc/nginx/conf.d/default.conf
fi

if [ -w /etc/nginx/conf.d/default.conf ] && grep -q 'listen 8000' /etc/nginx/conf.d/default.conf; then
    sed -i "s/listen 8000/listen ${PORT}/" /etc/nginx/conf.d/default.conf
fi

echo "Starting nginx on port ${PORT}..."
exec nginx -g 'daemon off;'
