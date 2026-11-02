#!/bin/sh
set -eu

SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
PROJECT_ROOT="$(CDPATH= cd -- "$SCRIPT_DIR/.." && pwd)"
DIST_FILE="$SCRIPT_DIR/.env.dist"
TARGET_FILE="$PROJECT_ROOT/.env"

if [ ! -f "$DIST_FILE" ]; then
    echo "Missing template: $DIST_FILE"
    exit 1
fi

if [ -f "$TARGET_FILE" ] && [ "${1:-}" != "--force" ]; then
    echo "$TARGET_FILE already exists. Use --force to overwrite."
    exit 0
fi

cp "$DIST_FILE" "$TARGET_FILE"
echo "Created $TARGET_FILE from $DIST_FILE"
