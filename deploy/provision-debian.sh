#!/bin/bash
# Installs Plataforma INIFAP (laravel-app) on Debian 12 or 13: Nginx, PHP-FPM, PostgreSQL, Node.js,
# ffmpeg and Ghostscript for compressing uploads, and a queue worker service that runs the compression.
#
# Usage, from a copy of this repository on the server:
#   sudo bash deploy/provision-debian.sh
#
# The database schema (1_biblioteca_inifap.sql and 2_biblioteca_seguridad.sql) is not in git.
# Put both files in laravel-app/ (where .gitignore expects them) or point SCHEMA_DIR at them.
#
# Safe to run again to redeploy: it keeps .env, uploaded files, the database and the admin password.
#
# Environment variables:
#   SRC             repository root (default: the parent of this script's directory)
#   SCHEMA_DIR      directory with the two schema SQL files (default: $SRC/laravel-app)
#   APP_URL         public URL for a new .env (default: http://<server IP>)
#   DB_PASS         database password for the first run (default: random). Later runs reuse the one in .env.
#   ADMIN_PASSWORD  password for admin@inifap.gob.mx when it is created (default: random, printed at the end)
set -euo pipefail

SRC=${SRC:-$(cd "$(dirname "$0")/.." && pwd)}
SCHEMA_DIR=${SCHEMA_DIR:-$SRC/laravel-app}
APP_DIR=/var/www/inifap
DB_NAME=inifap_dp
DB_USER=inifap_app
ADMIN_EMAIL=admin@inifap.gob.mx
CREDENTIALS=/root/inifap-admin.txt
WORKER=inifap-worker
# Vite 7 and pdfjs-dist need Node >= 22.13; Debian 12 ships 18 and Debian 13 ships 20.
NODE_MAJOR=22

[ "$(id -u)" = 0 ] || { echo "Run as root: sudo bash $0" >&2; exit 1; }
[ -d "$SRC/laravel-app" ] || { echo "No laravel-app/ in $SRC; set SRC to the repository root." >&2; exit 1; }
for f in 1_biblioteca_inifap.sql 2_biblioteca_seguridad.sql; do
    [ -f "$SCHEMA_DIR/$f" ] || { echo "Missing $SCHEMA_DIR/$f (the schema is not in git)." >&2; exit 1; }
done

random_secret() { head -c 16 /dev/urandom | od -An -tx1 | tr -d ' \n'; }
if [ -f "$APP_DIR/.env" ]; then
    DB_PASS=$(sed -n 's/^DB_PASSWORD=//p' "$APP_DIR/.env")
fi
DB_PASS=${DB_PASS:-$(random_secret)}

echo "==> 1. Packages"
export DEBIAN_FRONTEND=noninteractive
apt-get update -q
apt-get install -y -q nginx postgresql postgresql-client \
    php-cli php-fpm php-pgsql php-mbstring php-xml php-curl php-zip php-intl php-gd php-bcmath \
    composer unzip git rsync ca-certificates curl gnupg
# ffmpeg's recommends are GPU drivers (Mesa, LLVM, ~700 MB) that a server does not use.
apt-get install -y -q --no-install-recommends ffmpeg ghostscript
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
PHP_FPM=php$PHP_VERSION-fpm
echo "PHP $PHP_VERSION"
php -r 'exit(function_exists("imagewebp") ? 0 : 1);' \
    || echo "WARNING: PHP GD has no WebP support; images will be stored as uploaded." >&2

echo "==> 2. Node.js $NODE_MAJOR (NodeSource)"
if ! command -v node >/dev/null || ! node -e '
    const [major, minor] = process.versions.node.split(".").map(Number);
    process.exit(major > 22 || (major === 22 && minor >= 13) ? 0 : 1)'; then
    install -d -m 0755 /etc/apt/keyrings
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
        | gpg --dearmor --yes -o /etc/apt/keyrings/nodesource.gpg
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" \
        > /etc/apt/sources.list.d/nodesource.list
    apt-get update -q
    apt-get install -y -q nodejs
fi
echo "Node $(node --version)"

echo "==> 3. PostgreSQL role and database"
systemctl enable --now postgresql
if sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='$DB_USER'" | grep -q 1; then
    sudo -u postgres psql -v ON_ERROR_STOP=1 -qc "ALTER ROLE $DB_USER PASSWORD '$DB_PASS'"
else
    sudo -u postgres psql -v ON_ERROR_STOP=1 -qc "CREATE ROLE $DB_USER LOGIN PASSWORD '$DB_PASS'"
fi
sudo -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='$DB_NAME'" | grep -q 1 \
    || sudo -u postgres createdb -O "$DB_USER" "$DB_NAME"

echo "==> 4. Application files"
# Stop the worker so it does not pick up jobs while the code changes underneath it.
systemctl stop "$WORKER" 2>/dev/null || true
mkdir -p "$APP_DIR"
# Excluded paths are also kept on the server: rsync --delete does not touch them.
rsync -a --delete --exclude .env --exclude vendor --exclude node_modules --exclude public/build \
    --exclude 'storage/logs/*' --exclude 'storage/framework/sessions/*' --exclude 'storage/framework/views/*' \
    --exclude 'storage/framework/cache/*' --exclude 'storage/app/public/uploads' --exclude 'storage/app/medios-tmp' \
    --exclude public/storage \
    "$SRC/laravel-app/" "$APP_DIR/"
# The migration run_postgres_schema_scripts loads these two files from base_path().
cp "$SCHEMA_DIR/1_biblioteca_inifap.sql" "$SCHEMA_DIR/2_biblioteca_seguridad.sql" "$APP_DIR/"
chown -R www-data:www-data "$APP_DIR"

cd "$APP_DIR"
# www-data's home (/var/www) is not writable, so Composer and npm get their own cache directories.
install -d -o www-data -g www-data /var/cache/inifap/composer /var/cache/inifap/npm
as_web() { sudo -u www-data -H env COMPOSER_HOME=/var/cache/inifap/composer npm_config_cache=/var/cache/inifap/npm "$@"; }
set_env() {
    if grep -q "^$1=" .env; then sed -i "s|^$1=.*|$1=$2|" .env; else echo "$1=$2" >> .env; fi
}

echo "==> 5. Composer and .env"
as_web composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
if [ ! -f .env ]; then
    as_web cp .env.example .env
    APP_URL=${APP_URL:-http://$(hostname -I | awk '{print $1}')}
    sed -i \
        -e "s|^APP_NAME=.*|APP_NAME=\"Biblioteca INIFAP\"|" \
        -e "s|^APP_ENV=.*|APP_ENV=production|" \
        -e "s|^APP_DEBUG=.*|APP_DEBUG=false|" \
        -e "s|^APP_URL=.*|APP_URL=$APP_URL|" \
        -e "s|^APP_LOCALE=.*|APP_LOCALE=es|" \
        -e "s|^DB_CONNECTION=.*|DB_CONNECTION=pgsql\nDB_HOST=127.0.0.1\nDB_PORT=5432\nDB_DATABASE=$DB_NAME\nDB_USERNAME=$DB_USER\nDB_PASSWORD=$DB_PASS|" \
        -e "s|^SESSION_DRIVER=.*|SESSION_DRIVER=database\nSESSION_TABLE=sessions|" \
        -e "s|^CACHE_STORE=.*|CACHE_STORE=file|" \
        .env
    as_web php artisan key:generate --force
fi
# Uploaded PDFs and videos are compressed by the queue worker. A video can take a while, so the queue
# must not hand the same job to another worker before the job's own timeout (MEDIA_TIMEOUT + 60 s).
set_env QUEUE_CONNECTION database
set_env DB_QUEUE_RETRY_AFTER 3720
chown www-data:www-data .env
chmod 640 .env

echo "==> 6. Frontend assets (Vite)"
as_web npm ci --no-audit --no-fund
as_web npm run build

echo "==> 7. Database schema and data"
as_web php artisan migrate --force
ADMIN_CREATED=
if [ -z "$(sudo -u postgres psql -d "$DB_NAME" -tAc "SELECT 1 FROM usuarios WHERE email = '$ADMIN_EMAIL'")" ]; then
    as_web php artisan db:seed --class=AdminSeeder --force >/dev/null
    # AdminSeeder sets a fixed development password; replace it before the site is reachable.
    ADMIN_PASSWORD=${ADMIN_PASSWORD:-$(random_secret)}
    HASH=$(php -r 'echo password_hash($argv[1], PASSWORD_BCRYPT, ["cost" => 12]);' -- "$ADMIN_PASSWORD")
    sudo -u postgres psql -d "$DB_NAME" -v ON_ERROR_STOP=1 -q -v hash="$HASH" -v email="$ADMIN_EMAIL" <<'SQL'
UPDATE usuarios SET password_hash = :'hash' WHERE email = :'email';
SQL
    printf 'URL: %s\nUser: %s\nPassword: %s\n' "$(sed -n 's/^APP_URL=//p' .env)" "$ADMIN_EMAIL" "$ADMIN_PASSWORD" > "$CREDENTIALS"
    chmod 600 "$CREDENTIALS"
    ADMIN_CREATED=1
fi
# The legacy catalog (publications from the old static site) is loaded only into an empty database.
if [ "$(sudo -u postgres psql -d "$DB_NAME" -tAc 'SELECT COUNT(*) FROM publicaciones')" = "0" ]; then
    as_web php artisan db:seed --class=JsDataSeeder --force
fi

echo "==> 8. Storage link, permissions and caches"
as_web php artisan storage:link || true
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
as_web php artisan optimize:clear
as_web php artisan config:cache
as_web php artisan route:cache
as_web php artisan view:cache

echo "==> 9. PHP limits (videos up to 200 MB, images converted in memory)"
printf 'upload_max_filesize = 200M\npost_max_size = 210M\nmemory_limit = 512M\n' \
    > "/etc/php/$PHP_VERSION/fpm/conf.d/99-inifap.ini"

echo "==> 10. Queue worker ($WORKER.service)"
cat > "/etc/systemd/system/$WORKER.service" <<UNIT
[Unit]
Description=Plataforma INIFAP queue worker (compresses uploaded PDFs, videos and audio)
After=network.target postgresql.service

[Service]
User=www-data
Group=www-data
WorkingDirectory=$APP_DIR
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=1 --max-time=3600 --memory=512
Restart=always
RestartSec=5
# Conversions run behind the web server, which keeps answering requests at full priority.
Nice=10
IOSchedulingClass=best-effort
IOSchedulingPriority=7

[Install]
WantedBy=multi-user.target
UNIT
systemctl daemon-reload
systemctl enable "$WORKER"

echo "==> 11. Nginx"
cat > /etc/nginx/sites-available/inifap <<'NGINX'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /var/www/inifap/public;
    index index.php;
    charset utf-8;
    client_max_body_size 210M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Vite output has content hashes in the file names, so browsers can keep it forever.
    # The pdf.js worker is an ES module (.mjs), which nginx does not know: browsers only accept it as JavaScript.
    location ^~ /build/ {
        expires max;
        default_type application/javascript;
        try_files $uri =404;
    }

    # Uploads get a new random name whenever they change. Nothing under /storage is ever run as PHP.
    location ^~ /storage/ {
        expires 30d;
        try_files $uri =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:__PHP_SOCKET__;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}
NGINX
sed -i "s|__PHP_SOCKET__|/run/php/$PHP_FPM.sock|" /etc/nginx/sites-available/inifap
ln -sf /etc/nginx/sites-available/inifap /etc/nginx/sites-enabled/inifap
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable --now "$PHP_FPM" nginx
systemctl restart "$PHP_FPM" nginx "$WORKER"

echo "==> Done: $(sed -n 's/^APP_URL=//p' .env)/"
systemctl is-active --quiet "$WORKER" && echo "Queue worker: running" || echo "WARNING: $WORKER is not running; see journalctl -u $WORKER" >&2
if [ -n "$ADMIN_CREATED" ]; then
    echo "Admin account (also saved in $CREDENTIALS, readable only by root):"
    sed 's/^/    /' "$CREDENTIALS"
fi
