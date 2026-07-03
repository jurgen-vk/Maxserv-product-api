#!/bin/bash

for composer in /var/www/html/packages/*/composer.json; do
    name=$(grep '"name"' "$composer" | sed 's/.*: *"\(.*\)".*/\1/')
    link="/var/www/html/vendor/$name"

    mkdir -p "$(dirname "$link")"

    if [ ! -e "$link" ]; then
        pkg_dir=$(dirname "$composer")
        echo "Creating symlink: $link -> $pkg_dir"
        ln -sf "$pkg_dir" "$link"
    fi
done

exec apache2-foreground