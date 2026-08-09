FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist

COPY . .
RUN composer dump-autoload \
    --no-dev \
    --optimize \
    --no-interaction


FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.js tailwind.config.js ./
RUN npm run build


FROM php:8.3-fpm AS app

ENV APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/var/www/html/database/database.sqlite

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libsqlite3-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        opcache \
        pdo_sqlite \
        zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=composer /app ./
COPY --from=frontend /app/public/build ./public/build
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint

RUN chmod +x /usr/local/bin/docker-entrypoint \
    && mkdir -p storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache database

EXPOSE 9000

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]


FROM nginx:1.27-alpine AS nginx

WORKDIR /var/www/html

COPY --from=composer /app/public ./public
COPY --from=frontend /app/public/build ./public/build
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
