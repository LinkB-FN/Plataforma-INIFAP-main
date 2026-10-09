#!/bin/bash
# Builds the installation package for a client's Debian server: the app, the database schema, the
# frontend already built, the deploy scripts and the guide. The server then needs neither git nor Node.js.
#
# Usage, on a development machine with git and Node.js >= 22.13:
#   SCHEMA_DIR=/path/to/schema bash deploy/build-release.sh [output dir]
#
# SCHEMA_DIR holds 1_biblioteca_inifap.sql and 2_biblioteca_seguridad.sql (default: laravel-app/).
# The package is built from the last commit (HEAD); uncommitted changes are not included.
# Output: dist/plataforma-inifap-<date>-<commit>.tar.gz and its .sha256.
set -euo pipefail

ROOT=$(cd "$(dirname "$0")/.." && pwd)
SCHEMA_DIR=${SCHEMA_DIR:-$ROOT/laravel-app}
OUT=$(mkdir -p "${1:-$ROOT/dist}" && cd "${1:-$ROOT/dist}" && pwd)

for f in 1_biblioteca_inifap.sql 2_biblioteca_seguridad.sql; do
    [ -f "$SCHEMA_DIR/$f" ] || { echo "Missing $SCHEMA_DIR/$f; set SCHEMA_DIR." >&2; exit 1; }
done
[ -z "$(git -C "$ROOT" status --porcelain -- laravel-app deploy)" ] \
    || echo "Note: there are uncommitted changes; the package is built from HEAD without them." >&2

VERSION=$(date +%Y.%m.%d)-$(git -C "$ROOT" rev-parse --short HEAD)
NAME=plataforma-inifap-$VERSION
WORK=$(mktemp -d)
trap 'rm -rf "$WORK"' EXIT

git -C "$ROOT" archive --prefix="$NAME/" HEAD laravel-app deploy | tar -x -C "$WORK"
PKG=$WORK/$NAME
# Development leftovers that a server does not need.
rm -rf "$PKG/laravel-app/tmp" "$PKG"/laravel-app/tmp_*.php "$PKG/laravel-app/check.php" \
    "$PKG"/laravel-app/storage/vite-hot-stale-* "$PKG/deploy/build-release.sh"
cp "$SCHEMA_DIR/1_biblioteca_inifap.sql" "$SCHEMA_DIR/2_biblioteca_seguridad.sql" "$PKG/laravel-app/"
echo "$VERSION" > "$PKG/laravel-app/VERSION"
mv "$PKG/deploy/LEEME.txt" "$PKG/LEEME.txt"

echo "==> Building the frontend"
(cd "$PKG/laravel-app" && npm ci --no-audit --no-fund --loglevel=error && npm run build --silent && rm -rf node_modules)

tar -C "$WORK" -czf "$OUT/$NAME.tar.gz" "$NAME"
(cd "$OUT" && sha256sum "$NAME.tar.gz" > "$NAME.tar.gz.sha256")
echo "==> $OUT/$NAME.tar.gz ($(du -h "$OUT/$NAME.tar.gz" | cut -f1))"
