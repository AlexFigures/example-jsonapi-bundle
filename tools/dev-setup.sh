#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
export LOCAL_UID="${LOCAL_UID:-$(id -u)}"
export LOCAL_GID="${LOCAL_GID:-$(id -g)}"
docker compose up -d --build --wait
docker compose exec -T --user root php sh -c "mkdir -p vendor var/cache && chown -R ${LOCAL_UID}:${LOCAL_GID} vendor var"
docker compose exec -T php composer install --prefer-dist --no-interaction
docker compose exec -T php composer check-platform-reqs
for connection in pgsql mysql; do
    docker compose exec -T php php bin/console doctrine:database:create --connection="$connection" --if-not-exists
    docker compose exec -T php php bin/console doctrine:database:create --connection="$connection" --env=test --if-not-exists
done
for connection in pgsql mysql shard_b replica; do
    docker compose exec -T php php bin/console doctrine:database:create --connection="$connection" --env=torture --if-not-exists
done
# The command resets the disposable demo schemas and prints generated resource IDs.
docker compose exec -T php php bin/console app:acceptance:seed
docker compose exec -T php php tools/dev-http-smoke.php
