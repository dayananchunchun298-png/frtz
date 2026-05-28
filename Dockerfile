FROM php:8.2-fpm-bookworm

ARG INSTALL_DEV_DEPS=0

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    nginx \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql intl opcache zip gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

WORKDIR /app

COPY . .

# Skip post-install scripts (symfony-cmd) during image build; entrypoint warms cache at runtime.
RUN if [ ! -f .env ]; then cp .env.dist .env; fi \
    && if [ "$INSTALL_DEV_DEPS" = "1" ]; then \
         composer install --prefer-dist --no-scripts; \
       else \
         composer install --prefer-dist --no-dev --optimize-autoloader --no-scripts; \
       fi

# Compile front-end assets at build time (assets/vendor/ is committed; no CDN needed).
RUN APP_ENV=prod APP_DEBUG=0 APP_SECRET=DockerBuildOnlyNotUsedAtRuntime123 \
    php bin/console asset-map:compile

COPY docker/php-local.ini /usr/local/etc/php/conf.d/99-local.ini
COPY docker/nginx-main.conf /etc/nginx/nginx.conf
COPY docker/nginx.conf /etc/nginx/default-fpm.conf
COPY docker/nginx-php-server.conf /etc/nginx/default-builtin.conf
RUN rm -f /etc/nginx/conf.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

RUN mkdir -p var/cache var/log public/uploads config/jwt \
    && chown -R www-data:www-data var public/uploads config/jwt

ENV PORT=8000
EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
