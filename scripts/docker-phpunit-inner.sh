#!/usr/bin/env bash
set -euo pipefail
cd /app

export DEBIAN_FRONTEND=noninteractive
if command -v apt-get >/dev/null 2>&1; then
    if ! php -m | grep -q '^zip$'; then
        sed -i 's|deb.debian.org|archive.debian.org|g' /etc/apt/sources.list 2>/dev/null || true
        sed -i 's|security.debian.org|archive.debian.org|g' /etc/apt/sources.list 2>/dev/null || true
        find /etc/apt/sources.list.d -name '*.list' -exec sed -i 's|deb.debian.org|archive.debian.org|g' {} \; 2>/dev/null || true
        find /etc/apt/sources.list.d -name '*.list' -exec sed -i 's|security.debian.org|archive.debian.org|g' {} \; 2>/dev/null || true
        echo 'Acquire::Check-Valid-Until "false";' >/etc/apt/apt.conf.d/99no-check-valid-until
        apt-get update -qq
        apt-get install -y -qq unzip git zlib1g-dev $(
            case "$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')" in
                7.2) echo 'libzip4 libzip-dev' ;;
                *) echo 'libzip-dev' ;;
            esac
        )
        if [ "$(php -r 'echo PHP_MAJOR_VERSION;')" = 7 ]; then
            docker-php-ext-configure zip --with-libzip
        else
            docker-php-ext-configure zip
        fi
        docker-php-ext-install -j"$(nproc)" zip
    fi
fi

if ! command -v composer >/dev/null 2>&1; then
    EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r 'copy("https://getcomposer.org/installer", "composer-setup.php");'
    ACTUAL_CHECKSUM="$(php -r 'echo hash_file("sha384", "composer-setup.php");')"
    if [ "${EXPECTED_CHECKSUM}" != "${ACTUAL_CHECKSUM}" ]; then
        echo "Composer installer checksum mismatch" >&2
        exit 1
    fi
    php composer-setup.php --2.2 --install-dir=/usr/local/bin --filename=composer
    rm -f composer-setup.php
fi

if [ ! -d vendor ]; then
    composer update --no-interaction --prefer-dist --no-progress
fi

if php -m | grep -q '^intl$'; then
    echo "intl must not be loaded" >&2
    exit 1
fi

# shellcheck disable=SC2086
eval "php vendor/bin/phpunit ${PHPUNIT_ARGS}"
