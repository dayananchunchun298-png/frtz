#!/bin/sh
set -e

cd /app

# Railway public URL when APP_URL is not set explicitly
if [ -z "${APP_URL:-}" ] && [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ]; then
    export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
    export DEFAULT_URI="${APP_URL}"
fi

PORT="${PORT:-8000}"
PHP_RUNTIME="${PHP_RUNTIME:-auto}"

# Ensure .env exists (image ships .env.dist; compose may mount host .env)
if [ ! -f .env ]; then
    cp .env.dist .env
fi

echo "Waiting for database..."
i=0
while [ $i -lt 30 ]; do
    if php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; then
        break
    fi
    i=$((i + 1))
    sleep 1
done

echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then
    echo "Generating JWT key pair..."
    php bin/console lexik:jwt:generate-keypair --skip-if-exists
fi

if [ "${APP_ENV:-prod}" = "prod" ]; then
    echo "Warming production cache..."
    php bin/console cache:clear --no-warmup
    php bin/console cache:warmup
    php bin/console asset-map:compile 2>/dev/null || true
fi

chown -R www-data:www-data var public/uploads 2>/dev/null || true
chmod -R ug+rwX var public/uploads 2>/dev/null || true
if [ -d config/jwt ]; then
    chown -R www-data:www-data config/jwt 2>/dev/null || true
    chmod -R ug+rwX config/jwt 2>/dev/null || true
fi

use_fpm=0
if [ "$PHP_RUNTIME" = "fpm" ]; then
    use_fpm=1
elif [ "$PHP_RUNTIME" = "builtin" ]; then
    use_fpm=0
else
    php-fpm --nodaemonize 2>/dev/null &
    sleep 2
    if ss -tln 2>/dev/null | grep -q ':9000'; then
        use_fpm=1
    else
        kill %1 2>/dev/null || true
    fi
fi

if [ "$use_fpm" = "1" ]; then
    echo "Starting PHP-FPM..."
    if [ "$PHP_RUNTIME" != "fpm" ]; then
        : # already started during auto-detect
    else
        php-fpm --nodaemonize &
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
