#!/usr/bin/env bash
set -euo pipefail

cd "${1:-.}"

EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
if [ "${EXPECTED_CHECKSUM}" != "${ACTUAL_CHECKSUM}" ]; then
    echo "Composer installer checksum mismatch" >&2
    exit 1
fi
php composer-setup.php --2.2 --install-dir=/usr/local/bin --filename=composer
rm -f composer-setup.php
