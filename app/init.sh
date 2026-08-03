#!/usr/bin/env bash
#
# init.sh - Clone the "bomul" sample database into a new database named
# after a signup ID / shop ID, for initial shop setup.
#
# Usage:
#   ./init.sh <new_db_name> [source_db_name]
#
# DB connection info is read from the CodeIgniter .env file at
# app/../.env (i.e. the project root .env), from the database.default.*
# (or database_default_*) keys. Any of DB_HOST/DB_PORT/DB_USER/DB_PASS
# already set in the environment take precedence over the .env file.
#
# Examples:
#   ./init.sh shop1234
#   ./init.sh shop1234 bomul_sample
#   DB_PASS=secret ./init.sh shop1234

set -euo pipefail

SCRIPT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
PROJECT_ROOT=$(cd "${SCRIPT_DIR}/.." && pwd)
ENV_FILE="${PROJECT_ROOT}/.env"

SCRIPT_NAME=$(basename "$0")

usage() {
    echo "Usage: ${SCRIPT_NAME} <new_db_name> [source_db_name]" >&2
    echo "  new_db_name    signup ID / shop ID to create as the new database" >&2
    echo "  source_db_name database to clone from (default: bomul)" >&2
    exit 1
}

# Reads "database.default.<key>" (or the "database_default_<key>" variant
# CI4 also accepts) from the .env file, ignoring comments/blank lines and
# stripping surrounding quotes/whitespace.
env_get() {
    local key="$1"
    [ -f "$ENV_FILE" ] || return 0
    grep -E "^[[:space:]]*database[._]default[._]${key}[[:space:]]*=" "$ENV_FILE" \
        | tail -n 1 \
        | sed -E 's/^[^=]*=[[:space:]]*//; s/[[:space:]]*$//; s/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'
}

[ $# -ge 1 ] || usage

NEW_DB="$1"
SRC_DB="${2:-bomul}"

if [ ! -f "$ENV_FILE" ]; then
    echo "Warning: .env not found at ${ENV_FILE}, falling back to DB_* env vars / defaults" >&2
fi

DB_HOST="${DB_HOST:-$(env_get hostname)}"
DB_PORT="${DB_PORT:-$(env_get port)}"
DB_USER="${DB_USER:-$(env_get username)}"
if [ -z "${DB_PASS+x}" ]; then
    DB_PASS="$(env_get password)"
fi

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-root}"

# MySQL database identifiers: letters/digits/underscore, must start with a
# letter, max 64 bytes. Enforced here since NEW_DB/SRC_DB are interpolated
# into SQL identifiers below (mysqldump/mysql don't parameterize DB names).
DB_NAME_RE='^[a-zA-Z][a-zA-Z0-9_]{0,63}$'
for name in "$NEW_DB" "$SRC_DB"; do
    if [[ ! "$name" =~ $DB_NAME_RE ]]; then
        echo "Error: invalid database name '${name}' (must match ${DB_NAME_RE})" >&2
        exit 1
    fi
done

if [ "$NEW_DB" = "$SRC_DB" ]; then
    echo "Error: new_db_name must be different from source_db_name (${SRC_DB})" >&2
    exit 1
fi

# If no password came from the environment or .env, prompt for one
# interactively (an empty password is assumed non-interactively).
if [ -z "$DB_PASS" ] && [ -t 0 ]; then
    read -r -s -p "DB password for ${DB_USER}@${DB_HOST}: " DB_PASS
    echo
fi

# Keep the password out of argv / process list by using a defaults file.
MYSQL_CNF=$(mktemp)
trap 'rm -f "$MYSQL_CNF"' EXIT
cat > "$MYSQL_CNF" <<EOF
[client]
host=${DB_HOST}
port=${DB_PORT}
user=${DB_USER}
password=${DB_PASS}
EOF
chmod 600 "$MYSQL_CNF"

MYSQL=(mysql --defaults-extra-file="$MYSQL_CNF")
MYSQLDUMP=(mysqldump --defaults-extra-file="$MYSQL_CNF")

echo "==> Checking source database '${SRC_DB}' exists..."
if ! "${MYSQL[@]}" -N -e "SHOW DATABASES LIKE '${SRC_DB}';" | grep -q .; then
    echo "Error: source database '${SRC_DB}' does not exist" >&2
    exit 1
fi

echo "==> Checking target database '${NEW_DB}'..."
if "${MYSQL[@]}" -N -e "SHOW DATABASES LIKE '${NEW_DB}';" | grep -q .; then
    echo "Error: database '${NEW_DB}' already exists, aborting" >&2
    exit 1
fi

echo "==> Creating database '${NEW_DB}'..."
"${MYSQL[@]}" -e "CREATE DATABASE \`${NEW_DB}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

echo "==> Copying '${SRC_DB}' -> '${NEW_DB}'..."
"${MYSQLDUMP[@]}" --routines --triggers --events --single-transaction "${SRC_DB}" \
    | "${MYSQL[@]}" "${NEW_DB}"

echo "==> Done. '${SRC_DB}' copied to '${NEW_DB}'."
