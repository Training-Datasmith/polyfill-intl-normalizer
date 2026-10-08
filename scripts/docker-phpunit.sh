#!/usr/bin/env bash
set -euo pipefail

IMAGE="${1:?usage: docker-phpunit.sh IMAGE [phpunit args...]}"
shift

WORKDIR="/app"
REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"

if [ $# -eq 0 ]; then
    set -- --configuration phpunit.xml.dist
fi

docker run --rm -v "${REPO_ROOT}:${WORKDIR}" -w "${WORKDIR}" "${IMAGE}" \
    env PHPUNIT_ARGS="$*" \
    bash /app/scripts/docker-phpunit-inner.sh
