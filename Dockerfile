FROM node:22-alpine AS frontend

WORKDIR /app
COPY package.json ./
RUN npm install
COPY resources ./resources
COPY public ./public
COPY vite.config.ts tsconfig.json ./
RUN npm run build

FROM composer:2 AS backend

WORKDIR /app
COPY composer.json ./
RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts
COPY . .
RUN composer dump-autoload --optimize --no-interaction

FROM php:8.5-cli-alpine

RUN apk add --no-cache git icu-dev libzip-dev oniguruma-dev sqlite-dev \
    && docker-php-ext-install intl mbstring pdo_sqlite zip \
    && git clone --depth=1 https://github.com/etspring/pdd_russia.git /var/www/html/storage/app/pdd_russia

WORKDIR /var/www/html

COPY --from=backend /app /var/www/html
COPY --from=frontend /app/public/build /var/www/html/public/build
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint

RUN chmod +x /usr/local/bin/app-entrypoint \
    && mkdir -p database docker-config public/images/questions storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data database docker-config public/images storage bootstrap/cache

USER www-data

EXPOSE 8000

ENTRYPOINT ["app-entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
