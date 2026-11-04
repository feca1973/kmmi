#!/bin/sh
set -eu

# Local DEV installer: creates the Docker env file, starts the containers,
# installs the PHP dependencies, writes the Symfony local env files and creates the database.
# Safe to run again: existing env files are kept (use --force to regenerate them).

ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
cd "$ROOT"

force="0"
dump=""
for arg in "$@"; do
  case "$arg" in
    --force) force="1" ;;
    --dump=*) dump="${arg#--dump=}" ;;
    -h|--help)
      echo "Usage: sh scripts/project-init.sh [--force] [--dump=path/to/kmmi.sql]" >&2
      echo "  --dump  drops the dev database and loads the legacy SQL dump into it" >&2
      exit 0
      ;;
    *)
      echo "Unknown argument: $arg" >&2
      exit 1
      ;;
  esac
done

note() { printf '%s\n' "$*" >&2; }

random_hex() {
  if command -v openssl >/dev/null 2>&1; then
    openssl rand -hex "$1"
  else
    LC_ALL=C tr -dc 'a-f0-9' </dev/urandom | head -c "$(($1 * 2))"
  fi
}

if ! command -v docker >/dev/null 2>&1; then
  note "Docker is required."
  exit 1
fi

# 1. Docker Compose env file (.env)
if [ -f .env ] && [ "$force" != "1" ]; then
  note ".env already exists, keeping it (use --force to regenerate)."
else
  sed \
    -e "s/^MYSQL_ROOT_PASSWORD=.*/MYSQL_ROOT_PASSWORD=$(random_hex 24)/" \
    -e "s/^MYSQL_PASSWORD=.*/MYSQL_PASSWORD=$(random_hex 24)/" \
    docker/.env.dist > .env
  note "Created .env"
fi

set -a
# shellcheck disable=SC1091
. ./.env
set +a

# 2. Symfony local env files (not committed)
if [ -f site/.env.local ] && [ "$force" != "1" ]; then
  note "site/.env.local already exists, keeping it."
else
  cat > site/.env.local <<EOF
APP_SECRET=$(random_hex 32)
DATABASE_URL="mysql://${MYSQL_USER}:${MYSQL_PASSWORD}@mysql:3306/${MYSQL_DATABASE}?serverVersion=8.0&charset=utf8mb4"
DEFAULT_URI=http://localhost:${HTTP_PORT}
SEO_INDEXING_ENABLED=0
EOF
  note "Created site/.env.local"
fi

if [ -f site/.env.test.local ] && [ "$force" != "1" ]; then
  note "site/.env.test.local already exists, keeping it."
else
  cat > site/.env.test.local <<EOF
DATABASE_URL="mysql://${MYSQL_USER}:${MYSQL_PASSWORD}@mysql:3306/${MYSQL_DATABASE}?serverVersion=8.0&charset=utf8mb4"
EOF
  note "Created site/.env.test.local"
fi

# 3. Containers
docker compose up -d --build

# The first start initializes MySQL, which can take a while: wait until it accepts connections.
note "Waiting for MySQL..."
tries=0
until docker compose exec -T mysql mysqladmin ping -h 127.0.0.1 -uroot -p"${MYSQL_ROOT_PASSWORD}" --silent >/dev/null 2>&1; do
  tries=$((tries + 1))
  if [ "$tries" -gt 60 ]; then
    note "MySQL did not become ready in time."
    exit 1
  fi
  sleep 2
done

# The app user also owns the test databases (kmmi_test...): phpunit drops and recreates them.
# Done here, not in an init script, because init scripts only run when the MySQL volume is created.
docker compose exec -T -e MYSQL_PWD="${MYSQL_ROOT_PASSWORD}" mysql mysql -uroot \
  -e "GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}\\_test%\`.* TO '${MYSQL_USER}'@'%';"

# 4. Dependencies and database
mkdir -p site/public/uploads/images site/public/uploads/attachments
docker compose exec -T php composer install --no-interaction

if [ -n "$dump" ]; then
  if [ ! -f "$dump" ]; then
    note "Dump file not found: $dump"
    exit 1
  fi
  note "Loading the dump into ${MYSQL_DATABASE} (the existing dev data is dropped)..."
  docker compose exec -T -e MYSQL_PWD="${MYSQL_ROOT_PASSWORD}" mysql mysql -uroot \
    -e "DROP DATABASE IF EXISTS \`${MYSQL_DATABASE}\`; CREATE DATABASE \`${MYSQL_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  docker compose exec -T -e MYSQL_PWD="${MYSQL_ROOT_PASSWORD}" mysql mysql -uroot --default-character-set=utf8mb4 \
    "${MYSQL_DATABASE}" < "$dump"
fi

docker compose exec -T php php bin/console doctrine:database:create --if-not-exists --no-interaction
docker compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

if [ -n "$dump" ]; then
  # The search index is not in the dump: it is built from the loaded content.
  note "Building the search index..."
  docker compose exec -T php php bin/console app:search:reindex --no-interaction --no-ansi
fi

note ""
note "Ready."
note "  Site:       http://localhost:${HTTP_PORT}"
note "  phpMyAdmin: http://localhost:${PHPMYADMIN_PORT}"
