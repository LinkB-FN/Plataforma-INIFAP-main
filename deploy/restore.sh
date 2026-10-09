#!/bin/bash
# Restores a backup made by backup.sh: replaces the database and the uploaded files.
#
# Usage:
#   sudo bash deploy/restore.sh /var/backups/inifap/inifap-backup-YYYYMMDD-HHMMSS.tar
#
# Works on the same server or on a new one installed with provision-debian.sh. The current .env stays,
# because it holds this server's database password and URL; the backup's copy is in the archive as "env".
# The uploaded files being replaced are moved aside, not deleted. Set FORCE=1 to skip the confirmation.
set -euo pipefail

APP_DIR=/var/www/inifap
DB_NAME=inifap_dp
DB_USER=inifap_app
ARCHIVE=${1:-}

[ "$(id -u)" = 0 ] || { echo "Run as root: sudo bash $0 <backup.tar>" >&2; exit 1; }
[ -f "$ARCHIVE" ] || { echo "Usage: sudo bash $0 /var/backups/inifap/inifap-backup-....tar" >&2; exit 1; }
[ -f "$APP_DIR/.env" ] || { echo "No installation in $APP_DIR; run provision-debian.sh first." >&2; exit 1; }

WORK=$(mktemp -d)
trap 'rm -rf "$WORK"' EXIT
tar -C "$WORK" -xf "$ARCHIVE"
for f in database.dump uploads.tar; do
    [ -f "$WORK/$f" ] || { echo "$ARCHIVE is not a Plataforma INIFAP backup (no $f)." >&2; exit 1; }
done
chmod 755 "$WORK"
chmod 644 "$WORK/database.dump"

echo "Backup from: $(cat "$WORK/created" 2>/dev/null || echo unknown)"
echo "This replaces the current database and uploaded files with the ones in the backup."
if [ "${FORCE:-}" != 1 ]; then
    read -r -p "Type YES to continue: " answer
    [ "$answer" = YES ] || { echo "Cancelled."; exit 1; }
fi

PHP_FPM=php$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')-fpm
cd /
echo "==> Stopping the site"
systemctl stop inifap-worker "$PHP_FPM"
# Whatever happens next, bring the site back up.
trap 'systemctl start "$PHP_FPM" inifap-worker; rm -rf "$WORK"' EXIT

echo "==> Database"
sudo -u postgres dropdb --if-exists --force "$DB_NAME"
sudo -u postgres createdb -O "$DB_USER" "$DB_NAME"
sudo -u postgres pg_restore --no-owner --role="$DB_USER" --exit-on-error -d "$DB_NAME" "$WORK/database.dump"
# A backup from an older version may lack tables that this version adds.
sudo -u www-data php "$APP_DIR/artisan" migrate --force

echo "==> Uploaded files"
PUBLIC="$APP_DIR/storage/app/public"
if [ -d "$PUBLIC/uploads" ]; then
    ASIDE="$APP_DIR/storage/uploads-before-restore-$(date +%Y%m%d-%H%M%S)"
    mv "$PUBLIC/uploads" "$ASIDE"
    echo "The previous uploads were moved to $ASIDE; delete it once everything looks right."
fi
tar -C "$PUBLIC" -xf "$WORK/uploads.tar"
mkdir -p "$PUBLIC/uploads"
chown -R www-data:www-data "$PUBLIC"

echo "==> Caches"
sudo -u www-data php "$APP_DIR/artisan" cache:clear >/dev/null
echo "==> Restored from $ARCHIVE"
