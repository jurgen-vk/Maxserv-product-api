# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
COPY --parents packages/*/composer.json ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

FROM node:24-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
COPY --parents packages/*/package.json packages/*/vite.config.js packages/*/vite-plugins ./
RUN npm ci
COPY --parents packages/*/assets ./
COPY --parents packages/*/templates ./
RUN npm run build --workspaces --if-present

FROM php:8.5-apache AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends gosu && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql pcntl && \
    docker-php-ext-enable pdo pdo_mysql pcntl

RUN a2enmod rewrite headers

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
    printf '<Directory "${APACHE_DOCUMENT_ROOT}">\n\tAllowOverride All\n</Directory>\n' \
        >> /etc/apache2/apache2.conf && \
    printf '<Directory "${APACHE_DOCUMENT_ROOT}/assets/build">\n\tHeader set Cache-Control "public, max-age=31536000, immutable"\n</Directory>\n' \
        >> /etc/apache2/apache2.conf && \
    printf '<Directory "${APACHE_DOCUMENT_ROOT}/assets/icons">\n\tHeader set Cache-Control "public, max-age=3600, must-revalidate"\n</Directory>\n' \
        >> /etc/apache2/apache2.conf && \
    printf '<Directory "${APACHE_DOCUMENT_ROOT}/assets/site">\n\tHeader set Cache-Control "public, max-age=3600, must-revalidate"\n</Directory>\n' \
        >> /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/assets ./public/assets

ENV APP_ENV=prod
ENV MERCURE_HUB_URL=""
ENV MERCURE_PUBLIC_URL=""
ENV MERCURE_PUBLISHER_JWT=""
ENV VITE_SERVER_URL=""
ENV APP_URL=""

USER www-data
RUN php bin/console cache:warmup
USER root

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
