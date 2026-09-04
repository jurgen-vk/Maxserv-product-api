#!/bin/sh
set -e

if [ "$APP_ENV" = "dev" ]; then
    TARGET_UID="$(stat -c '%u' /var/www/html)"
    TARGET_GID="$(stat -c '%g' /var/www/html)"
    CURRENT_UID="$(id -u www-data)"
    CURRENT_GID="$(id -g www-data)"

    if [ "$TARGET_UID" != "0" ] && { [ "$TARGET_UID" != "$CURRENT_UID" ] || [ "$TARGET_GID" != "$CURRENT_GID" ]; }; then
        groupmod -g "$TARGET_GID" www-data
        usermod -u "$TARGET_UID" -g "$TARGET_GID" www-data
        chown -R www-data:www-data /var/www/html/var/cache 2>/dev/null || true
    fi
fi

if [ "$1" = "apache2-foreground" ]; then
    exec "$@"
fi

exec gosu www-data "$@"
