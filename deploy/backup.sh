#!/bin/bash
# Backs up Plataforma INIFAP into one archive: the PostgreSQL database, the uploaded files and .env.
#
# Usage:
#   sudo bash deploy/backup.sh           make a backup now
#   sudo bash deploy/backup.sh --daily   make one now and schedule one every day at 02:30
#
# Environment variables:
#   BACKUP_DIR  where the archives go (default: /var/backups/inifap)
#   KEEP        how many archives to keep; older ones are deleted (default: 7)
set -euo pipefail

APP_DIR=/var/www/inifap
DB_NAME=inifap_dp
BACKUP_DIR=${BACKUP_DIR:-/var/backups/inifap}
KEEP=${KEEP:-7}

[ "$(id -u)" = 0 ] || { echo "Run as root: sudo bash $0" >&2; exit 1; }
[ -f "$APP_DIR/.env" ] || { echo "No installation in $APP_DIR; run provision-debian.sh first." >&2; exit 1; }

if [ "${1:-}" = "--daily" ]; then
    # The timer runs a copy, so it keeps working if the installation package is deleted.
    install -m 0755 "$(readlink -f "$0")" /usr/local/sbin/inifap-backup
    cat > /etc/systemd/system/inifap-backup.service <<UNIT
[Unit]
Description=Plataforma INIFAP backup
After=postgresql.service

[Service]
Type=oneshot
Environment=BACKUP_DIR=$BACKUP_DIR KEEP=$KEEP
ExecStart=/usr/local/sbin/inifap-backup
Nice=10
IOSchedulingClass=idle
UNIT
    cat > /etc/systemd/system/inifap-backup.timer <<'UNIT'
[Unit]
Description=Daily Plataforma INIFAP backup

[Timer]
OnCalendar=*-*-* 02:30
Persistent=true

[Install]
WantedBy=timers.target
UNIT
    systemctl daemon-reload
    systemctl enable --now inifap-backup.timer
    echo "Daily backup scheduled at 02:30 in $BACKUP_DIR, keeping the last $KEEP."
fi

install -d -m 0700 "$BACKUP_DIR"
WORK=$(mktemp -d)
trap 'rm -rf "$WORK"' EXIT
cd /

# pg_dump's custom format is already compressed, and so are images and videos: the archive is a plain tar.
sudo -u postgres pg_dump -Fc "$DB_NAME" > "$WORK/database.dump"
cp "$APP_DIR/.env" "$WORK/env"
if [ -d "$APP_DIR/storage/app/public/uploads" ]; then
    tar -C "$APP_DIR/storage/app/public" -cf "$WORK/uploads.tar" uploads
else
    tar -cf "$WORK/uploads.tar" --files-from /dev/null
fi
date -Is > "$WORK/created"

ARCHIVE="$BACKUP_DIR/inifap-backup-$(date +%Y%m%d-%H%M%S).tar"
n=1
while [ -e "$ARCHIVE" ]; do ARCHIVE="$BACKUP_DIR/inifap-backup-$(date +%Y%m%d-%H%M%S)-$((n++)).tar"; done
tar -C "$WORK" -cf "$ARCHIVE.part" created database.dump env uploads.tar
chmod 600 "$ARCHIVE.part"
mv "$ARCHIVE.part" "$ARCHIVE"

ls -1t "$BACKUP_DIR"/inifap-backup-*.tar | tail -n +"$((KEEP + 1))" | xargs -r rm -f
echo "Backup created: $ARCHIVE ($(du -h "$ARCHIVE" | cut -f1))"
echo "Copy it to another machine too: a backup on the same disk does not survive a disk failure."
