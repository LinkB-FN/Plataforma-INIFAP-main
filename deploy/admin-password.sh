#!/bin/bash
# Sets a new password for a user of the admin panel and prints it, for when the password was lost.
# It also re-activates the account if it had been deactivated.
#
# Usage:
#   sudo bash deploy/admin-password.sh                      admin@inifap.gob.mx, random password
#   sudo bash deploy/admin-password.sh someone@example.org  another user
#
# Set NEW_PASSWORD to choose the password instead of a random one.
set -euo pipefail

DB_NAME=inifap_dp
EMAIL=${1:-admin@inifap.gob.mx}
CREDENTIALS=/root/inifap-admin.txt

[ "$(id -u)" = 0 ] || { echo "Run as root: sudo bash $0 [email]" >&2; exit 1; }
cd /
EXISTS=$(sudo -u postgres psql -d "$DB_NAME" -tA -v email="$EMAIL" <<'SQL'
SELECT 1 FROM usuarios WHERE email = :'email';
SQL
)
[ "$EXISTS" = 1 ] || { echo "There is no user with email $EMAIL." >&2; exit 1; }

PASSWORD=${NEW_PASSWORD:-$(head -c 16 /dev/urandom | od -An -tx1 | tr -d ' \n')}
HASH=$(php -r 'echo password_hash($argv[1], PASSWORD_BCRYPT, ["cost" => 12]);' -- "$PASSWORD")
sudo -u postgres psql -d "$DB_NAME" -v ON_ERROR_STOP=1 -q -v hash="$HASH" -v email="$EMAIL" <<'SQL'
UPDATE usuarios SET password_hash = :'hash', activo = TRUE WHERE email = :'email';
SQL

if [ "$EMAIL" = admin@inifap.gob.mx ]; then
    URL=$(sed -n 's/^APP_URL=//p' /var/www/inifap/.env)
    printf 'URL: %s\nUser: %s\nPassword: %s\n' "$URL" "$EMAIL" "$PASSWORD" > "$CREDENTIALS"
    chmod 600 "$CREDENTIALS"
fi
echo "New password for $EMAIL: $PASSWORD"
