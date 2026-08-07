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

# Dev overrides both of these via docker-compose.yml's runtime `environment:` block regardless —
# needed here as real, defined values (empty is fine, merely unset is not) because %env(...)%
# throws EnvNotFoundException on a genuinely-absent var, and cache:warmup below actually
# constructs Twig\Environment (for the template cache), which needs MERCURE_PUBLIC_URL
# resolvable for its addGlobal() call. The value itself never gets baked into the compiled
# cache — %env(...)% parameters are resolved lazily, at real request time, against whatever
# the container's actual environment is then, not whatever existed here at build time.
ENV APP_ENV=prod
ENV MERCURE_HUB_URL=""
ENV MERCURE_PUBLIC_URL=""
ENV MERCURE_PUBLISHER_JWT=""
ENV VITE_SERVER_URL=""
ENV APP_URL=""

# Run as www-data, not root, so the resulting cache files are already correctly owned in the
# image itself. This matters beyond the image: docker-compose.override.yml mounts a named
# volume over var/cache for local dev, and Docker seeds a brand-new empty volume from whatever
# the image already has at that path — including ownership. Baking a warm, www-data-owned cache
# here means that seed is already correct in the common case; entrypoint.sh re-owns it on
# startup for the dev case where www-data's id gets remapped after this point.
USER www-data
RUN php bin/console cache:warmup
USER root

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
