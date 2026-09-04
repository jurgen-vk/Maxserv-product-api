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

    for composer in /var/www/html/packages/*/composer.json; do
        name=$(php -r "echo json_decode(file_get_contents('$composer'))->name;")
        link="/var/www/html/vendor/$name"

        mkdir -p "$(dirname "$link")"

        if [ ! -e "$link" ]; then
            pkg_dir=$(dirname "$composer")
            echo "Creating symlink: $link -> $pkg_dir"
            ln -sf "$pkg_dir" "$link"
        fi
    done
fi

if [ "$1" = "apache2-foreground" ]; then
    exec "$@"
fi

exec gosu www-data "$@"
