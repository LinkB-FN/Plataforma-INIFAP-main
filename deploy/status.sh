#!/bin/bash
# Shows whether Plataforma INIFAP is healthy: services, web response, compression queue, disk, backups
# and recent errors. Changes nothing.
#
# Usage: sudo bash deploy/status.sh
set -uo pipefail

APP_DIR=/var/www/inifap
DB_NAME=inifap_dp
BACKUP_DIR=${BACKUP_DIR:-/var/backups/inifap}

[ "$(id -u)" = 0 ] || { echo "Run as root: sudo bash $0" >&2; exit 1; }
cd /
ok() { printf '  %-28s %s\n' "$1" "$2"; }
sql() { sudo -u postgres psql -d "$DB_NAME" -tAc "$1" 2>/dev/null || echo "?"; }

PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "?")
echo "Services"
for s in nginx "php$PHP_VERSION-fpm" postgresql inifap-worker; do
    ok "$s" "$(systemctl is-active "$s" 2>/dev/null)"
done

echo "Website"
ok "http://127.0.0.1/" "HTTP $(curl -s -o /dev/null -m 10 -w '%{http_code}' http://127.0.0.1/publicaciones)"
ok "URL in .env" "$(sed -n 's/^APP_URL=//p' "$APP_DIR/.env" 2>/dev/null)"

echo "Content"
ok "publications" "$(sql 'SELECT COUNT(*) FROM publicaciones')"
ok "users" "$(sql 'SELECT COUNT(*) FROM usuarios')"
ok "files waiting to compress" "$(sql 'SELECT COUNT(*) FROM jobs')"
ok "failed compressions" "$(sql 'SELECT COUNT(*) FROM failed_jobs')"

echo "Disk"
ok "free on /" "$(df -h --output=avail / | tail -1 | tr -d ' ')"
ok "uploaded files" "$(du -sh "$APP_DIR/storage/app/public/uploads" 2>/dev/null | cut -f1 || echo 0)"
ok "database" "$(sql "SELECT pg_size_pretty(pg_database_size('$DB_NAME'))")"

echo "Backups ($BACKUP_DIR)"
LAST=$(ls -1t "$BACKUP_DIR"/inifap-backup-*.tar 2>/dev/null | head -1)
if [ -n "$LAST" ]; then
    ok "latest" "$(basename "$LAST") ($(du -h "$LAST" | cut -f1))"
else
    ok "latest" "none"
fi
if systemctl is-enabled --quiet inifap-backup.timer 2>/dev/null; then
    ok "daily schedule" "$(systemctl show inifap-backup.timer -p NextElapseUSecRealtime --value | sed 's/^/next: /')"
else
    ok "daily schedule" "not scheduled (backup.sh --daily)"
fi

echo "Versions"
ok "Debian" "$(cat /etc/debian_version)"
ok "PHP" "$PHP_VERSION"
ok "PostgreSQL" "$(sudo -u postgres psql -tAc 'SHOW server_version' 2>/dev/null)"
ok "ffmpeg" "$(ffmpeg -version 2>/dev/null | head -1 | awk '{print $3}')"
ok "Ghostscript" "$(gs --version 2>/dev/null)"
ok "package" "$(cat "$APP_DIR/VERSION" 2>/dev/null || echo "unknown")"

echo "Last errors in storage/logs/laravel.log"
grep -h '\.ERROR:' "$APP_DIR/storage/logs/laravel.log" 2>/dev/null | tail -5 | cut -c1-220 | sed 's/^/  /' || true
grep -q '\.ERROR:' "$APP_DIR/storage/logs/laravel.log" 2>/dev/null || echo "  none"
